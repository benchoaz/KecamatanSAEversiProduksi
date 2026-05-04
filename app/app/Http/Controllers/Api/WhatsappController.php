<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WhatsappSession;
use App\Models\WhatsappLog;
use App\Models\ModuleSetting;
use App\Services\WhatsApp\IntentHandler;
use App\Services\WhatsApp\StateHandler;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WhatsappController extends Controller
{
    protected IntentHandler $intentHandler;
    protected StateHandler $stateHandler;

    public function __construct(IntentHandler $intentHandler, StateHandler $stateHandler)
    {
        $this->intentHandler = $intentHandler;
        $this->stateHandler = $stateHandler;
    }

    /**
     * Main WhatsApp message handler
     * This is the single endpoint that n8n calls
     */
    public function handle(Request $request): JsonResponse
    {
        // =====================================================
        // TRANSFORM WAHA WEBHOOK FORMAT
        // n8n sends: { phone: null, message: { payload: { from, body } } }
        // We need: { phone: string, message: string }
        // =====================================================

        $phone = $request->input('phone');
        $message = $request->input('message');

        // Check if message is an array (WAHA event object from n8n)
        if (is_array($message) && isset($message['payload'])) {
            $payload = $message['payload'];

            // Extract phone from payload.from
            if (isset($payload['from'])) {
                $phone = $payload['from'];
                // Strip @c.us or @s.whatsapp.net or @newsletter suffix
                $phone = preg_replace('/@(c\.us|s\.whatsapp\.net|newsletter)$/', '', $phone);
            }

            // Extract message text from payload.body
            $message = $payload['body'] ?? $payload['text'] ?? '';

            \Log::info('WAHA n8n format detected, transforming data', [
                'original_from' => $payload['from'] ?? null,
                'transformed_phone' => $phone,
                'message' => $message,
            ]);

            // Replace request data with transformed data
            $request->merge([
                'phone' => $phone,
                'message' => $message
            ]);
        }
        // Check if this is a direct WAHA webhook format (data.from and data.body)
        elseif ($request->has('data.from') && $request->has('data.body')) {
            $wahaData = $request->input('data');
            $phone = $wahaData['participant'] ?? $wahaData['author'] ?? $wahaData['from'];
            $phone = explode('@', $phone)[0];
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }
            $message = $wahaData['body'] ?? $wahaData['text'] ?? '';

            \Log::info('WAHA webhook format detected, transforming data', [
                'original_from' => $wahaData['from'],
                'transformed_phone' => $phone,
                'message' => $message,
            ]);

            // Replace request data with transformed data
            $request->merge([
                'phone' => $phone,
                'message' => $message
            ]);
        }

        // 1. FILTER BROADCAST / STATUS (CRITICAL)
        $originalPhone = $request->input('phone', '');
        if (str_contains($originalPhone, 'status@broadcast') || $originalPhone === 'status') {
            return response()->json([
                'success' => true,
                'message' => 'Ignoring status broadcast event'
            ]);
        }

        // Validate request
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        $phone = $request->input('phone');
        $message = trim($request->input('message'));

        $startTime = microtime(true);

        try {
            // Check maintenance mode
            if ($this->isMaintenanceMode()) {
                return $this->successResponse(
                    'Maaf, sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.'
                );
            }

            // Get or create session
            $session = WhatsappSession::getOrCreate($phone);

            // Clear stale sessions
            if ($session->isStale()) {
                $session->clear();
            }

            // Route to state handler or intent detector
            \Log::info('Bot Routing Path', [
                'phone' => $phone,
                'state' => $session->state,
                'is_active' => $session->isActive(),
                'message' => $message
            ]);

            if ($session->isActive()) {
                $response = $this->stateHandler->handle($session, $message);
            } else {
                $response = $this->intentHandler->handle($phone, $message);
            }

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $replyPreview = 'NULL';
            if (isset($response['reply'])) {
                $replyText = is_array($response['reply']) ? json_encode($response['reply']) : $response['reply'];
                $replyPreview = is_string($replyText) ? substr($replyText, 0, 50) . '...' : 'Not a string';
            }

            \Log::info('Bot Handler Response', [
                'intent' => $response['intent'] ?? 'N/A',
                'state_update' => $response['state_update'] ?? 'N/A',
                'duration_ms' => $duration . 'ms',
                'reply_preview' => $replyPreview
            ]);

            if (isset($response['state_update'])) {
                if ($response['state_update'] === null) {
                    $session->clear();
                } else {
                    $session->updateState($response['state_update']);
                }
            }

            if (empty($response['reply'])) {
                $response['reply'] = '🙏 Sistem sedang memproses. Silakan coba kembali beberapa saat lagi.';
            }

            $replyForLog = is_array($response['reply'] ?? '') ? json_encode($response['reply']) : (string) ($response['reply'] ?? '');

            WhatsappLog::logInteraction(
                $phone,
                $message,
                $response['intent'] ?? null,
                $replyForLog,
                $response['success'] ?? true
            );

            $response['chatId'] = $request->input('chatId');
            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('Bot Handler Error: ' . $e->getMessage());
            
            WhatsappLog::logInteraction(
                $phone,
                $message,
                'error',
                $e->getMessage(),
                false
            );

            return $this->errorResponse(
                "🙏 *Mohon maaf*, sepertinya saya sedikit kebingungan memproses pesan tersebut.\n\n" .
                "Silakan ketik *MENU* untuk kembali ke layanan utama kami. Terima kasih atas kesabarannya! 😊"
            );
        }
    }

    protected function isMaintenanceMode(): bool
    {
        return ModuleSetting::where('key', 'whatsapp_maintenance_mode')
            ->where('value', '1')
            ->exists();
    }

    protected function successResponse(string $reply, ?string $stateUpdate = null): JsonResponse
    {
        return response()->json([
            'success' => true,
            'reply' => $reply,
            'state_update' => $stateUpdate,
        ]);
    }

    protected function errorResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'reply' => $message,
            'state_update' => null,
        ]);
    }
}
