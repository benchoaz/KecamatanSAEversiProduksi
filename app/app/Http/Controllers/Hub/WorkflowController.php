<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WorkflowController extends Controller
{
    public function index()
    {
        // Simulasi status n8n (bisa dihubungkan ke API n8n nantinya)
        $n8n_url = config('services.n8n.url', 'http://localhost:5678');
        $is_online = false;
        
        try {
            $response = Http::timeout(2)->get($n8n_url . '/healthz');
            $is_online = $response->successful();
        } catch (\Exception $e) {
            $is_online = false;
        }

        return view('hub.workflow.index', compact('is_online', 'n8n_url'));
    }
}
