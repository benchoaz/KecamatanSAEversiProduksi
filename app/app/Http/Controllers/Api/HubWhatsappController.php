<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Hub\GatewayRouterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HubWhatsappController extends Controller
{
    protected $router;

    public function __construct(GatewayRouterService $router)
    {
        $this->router = $router;
    }

    /**
     * Main entry point for the Kabupaten Gateway WhatsApp routing.
     */
    public function route(Request $request)
    {
        $payload = $request->all();
        $from = $payload['from'] ?? null;
        $text = $payload['text'] ?? '';

        if (! $from) {
            return response()->json(['status' => 'error', 'message' => 'No sender info'], 400);
        }

        // 1. Cek apakah nomor ini sudah terikat ke kecamatan tertentu
        $district = $this->router->getDistrictByPhone($from);

        if ($district) {
            // Forward ke kecamatan tersebut
            return $this->forwardToDistrict($district, $payload);
        }

        // 2. Jika belum terikat, cek apakah ini perintah untuk memilih kecamatan
        // (Misal: user mengetik nama kecamatan)
        $activeDistricts = $this->router->getActiveDistricts();
        foreach ($activeDistricts as $d) {
            if (stripos($text, $d->name) !== false || stripos($text, $d->slug) !== false) {
                $this->router->setPhoneDistrict($from, $d->id);

                return response()->json([
                    'status' => 'success',
                    'reply' => "Selamat Datang! Anda kini terhubung dengan Layanan Digital {$d->name}. Silakan sampaikan keperluan Anda.",
                ]);
            }
        }

        // 3. Jika tidak ada konteks, berikan pilihan kecamatan
        $list = $activeDistricts->map(fn ($d) => "- {$d->name}")->implode("\n");

        return response()->json([
            'status' => 'success',
            'reply' => "Halo! Ini adalah Layanan Digital Kabupaten Probolinggo. \n\nSilakan ketik nama kecamatan Anda untuk melanjutkan:\n\n{$list}",
        ]);
    }

    /**
     * Meneruskan pesan ke internal API kecamatan.
     */
    protected function forwardToDistrict($district, $payload)
    {
        // Di sini kita bisa memanggil URL internal kecamatan
        // Untuk local dev, kita bisa menggunakan routing internal Laravel
        // atau memanggil endpoint API kecamatan yang bersangkutan.

        Log::info("Gateway Hub: Forwarding message from {$payload['from']} to district {$district->name}");

        // Simulasi forward (nanti bisa dihubungkan ke WhatsappController kecamatan)
        return response()->json([
            'status' => 'forwarded',
            'target' => $district->slug,
            'payload' => $payload,
        ]);
    }
}
