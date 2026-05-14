<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class WorkflowController extends Controller
{
    public function index()
    {
        // Ambil URL n8n dari env — bukan hardcode localhost
        $n8n_url   = env('N8N_URL', 'http://localhost:5678');
        $n8n_key   = env('N8N_API_KEY', '');
        $is_online = false;
        $workflows = collect();

        try {
            // Health check ke n8n Cloud
            $health = Http::timeout(3)->get($n8n_url . '/healthz');
            $is_online = $health->successful();

            // Jika online & ada API key → ambil daftar workflow
            if ($is_online && $n8n_key) {
                $wf_response = Http::timeout(5)
                    ->withHeaders(['X-N8N-API-KEY' => $n8n_key])
                    ->get($n8n_url . '/api/v1/workflows');

                if ($wf_response->successful()) {
                    $workflows = collect($wf_response->json('data') ?? []);
                }
            }
        } catch (\Exception $e) {
            $is_online = false;
        }

        return view('hub.workflow.index', compact('is_online', 'n8n_url', 'workflows'));
    }
}
