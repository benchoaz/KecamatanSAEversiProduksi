<?php

namespace App\Http\Controllers\Desa;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $desa_id = $user->desa_id;
        abort_unless($desa_id !== null, 403);

        $logs = AuditLog::whereHas('user', function ($query) use ($desa_id) {
            $query->where('desa_id', $desa_id);
        })
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('desa.audit.index', compact('logs'));
    }

    public function show($id)
    {
        $user = auth()->user();
        $desa_id = $user->desa_id;

        $log = AuditLog::with('user')->findOrFail($id);

        // Security check: only see logs from the same village
        abort_unless($log->user->desa_id === $desa_id, 403);

        return view('desa.audit.show', compact('log'));
    }
}
