<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        $userRole = $user && $user->role ? $user->role->name : 'NULL';

        // NUCLEAR BYPASS: Always allow the core 'admin' user or super_admin_kabupaten
        if ($user && ($user->username === 'admin' || $user->hasRole('super_admin_kabupaten'))) {
            return $next($request);
        }

        if (!$user) {
            return redirect('login');
        }

        // Strict Check temporarily disabled? No, let's keep it but handle the case.
        if (!$user->isModuleAdmin() && !in_array($userRole, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized domain access.'], 403);
            }

            abort(403, 'Anda tidak memiliki hak akses untuk masuk ke domain ini.');
        }

        return $next($request);
    }
}
