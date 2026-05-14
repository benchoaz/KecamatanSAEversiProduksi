<?php

namespace App\Http\Middleware\Hub;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HubAdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Hanya Super Admin (level 0/1) yang boleh masuk ke Hub Gateway
        if ($user && ($user->username === 'admin' || $user->hasRole('Super Admin'))) {
            return $next($request);
        }

        Auth::logout();

        return redirect()->route('hub.login')->with('error', 'Akses ditolak. Area khusus Super Admin Kabupaten.');
    }
}
