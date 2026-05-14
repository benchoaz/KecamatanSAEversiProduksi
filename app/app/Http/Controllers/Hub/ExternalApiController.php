<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use App\Models\Hub\HubExternalApp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExternalApiController extends Controller
{
    public function index()
    {
        $apps = HubExternalApp::orderBy('name')->get();

        return view('hub.api.index', compact('apps'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'scopes' => 'required|array',
            'base_url' => 'nullable|url',
        ]);

        HubExternalApp::create([
            'name' => $validated['name'],
            'client_id' => 'KAB_'.strtoupper(Str::random(10)),
            'client_secret' => Str::random(40),
            'base_url' => $validated['base_url'],
            'settings' => [
                'scopes' => $validated['scopes'],
            ],
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'Aplikasi Eksternal berhasil didaftarkan!');
    }

    public function toggleStatus($id)
    {
        $app = HubExternalApp::findOrFail($id);
        $app->status = ($app->status == 'active') ? 'inactive' : 'active';
        $app->save();

        return redirect()->back()->with('success', 'Status aplikasi '.$app->name.' berhasil diubah!');
    }
}
