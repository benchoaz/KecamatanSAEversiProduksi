<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    /**
     * Handle an incoming request for API authentication.
     * 
     * Supports both:
     * - Database-stored tokens (with abilities and expiration)
     * - Legacy env-based token (WHATSAPP_API_TOKEN)
     * 
     * @param Request $request
     * @param Closure $next
     * @param string|null $ability Required ability for this endpoint
     */
    public function handle(Request $request, Closure $next, ?string $ability = null): Response
    {
        // 1. Try Bearer token
        $token = $request->bearerToken();

        // 2. Fallback to custom header X-API-TOKEN (in case Authorization is stripped)
        if (empty($token)) {
            $token = $request->header('X-API-TOKEN');
        }

        if (empty($token)) {
            // Debug: Log headers if token is missing
            \Log::warning('API access attempt with NO token provided', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'headers' => collect($request->headers->all())->map(fn($h) => $h[0])->toArray()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. No API token provided.'
            ], 401);
        }

        // First, try database token validation
        $apiToken = $this->validateDatabaseToken($token);

        if ($apiToken) {
            // Check if token has required ability
            if ($ability && !$apiToken->can($ability)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden. Token lacks required ability: ' . $ability
                ], 403);
            }

            // Update last used timestamp
            $apiToken->markAsUsed();

            // Add token to request for controllers
            $request->attributes->set('api_token', $apiToken);

            return $next($request);
        }

        $whatsappToken = config('services.api_tokens.whatsapp');
        $dashboardToken = config('services.api_tokens.dashboard');
        
        // Dynamic token from Dashboard
        $n8nSetting = \App\Models\WahaN8nSetting::getSettings();
        $dbN8nToken = $n8nSetting ? $n8nSetting->n8n_token : null;

        if ((!empty($whatsappToken) && $token === $whatsappToken) || 
            (!empty($dashboardToken) && $token === $dashboardToken) ||
            (!empty($dbN8nToken) && $token === $dbN8nToken)) {
            return $next($request);
        }

        // Log unauthorized attempt
        \Log::warning('Unauthorized API access attempt with INVALID token', [
            'ip' => $request->ip(),
            'path' => $request->path(),
            'token_prefix' => substr($token, 0, 8) . '...'
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Unauthorized. Invalid or revoked API token.'
        ], 401);
    }

    /**
     * Validate a token against the database.
     */
    private function validateDatabaseToken(string $plainToken): ?ApiToken
    {
        $hashedToken = ApiToken::hashToken($plainToken);

        $apiToken = ApiToken::where('token', $hashedToken)->first();

        if (!$apiToken) {
            return null;
        }

        // Check if token is valid (not revoked, not expired)
        if (!$apiToken->isValid()) {
            return null;
        }

        return $apiToken;
    }
}
