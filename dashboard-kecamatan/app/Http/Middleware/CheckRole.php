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
        $logFile = storage_path('logs/CheckRole_Full.log');
        $user = $request->user();
        $userRole = $user && $user->role ? $user->role->nama_role : 'NULL';
        $logMsg = date('H:i:s') . " | User: " . ($user ? $user->username : 'Guest') . " | Role: " . $userRole . " | URL: " . $request->fullUrl() . " | Allowed: " . implode(',', $roles) . PHP_EOL;
        file_put_contents($logFile, $logMsg, FILE_APPEND);

        if (!$user) {
            return redirect('login');
        }

        // Strict Check temporarily disabled? No, let's keep it but handle the case.
        if (!$request->user()->isAdminPelayanan() && !in_array($userRole, $roles)) {
            file_put_contents($logFile, "DENIED! " . $userRole . " NOT IN " . implode(',', $roles) . PHP_EOL, FILE_APPEND);
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized domain access.'], 403);
            }

            abort(403, 'Anda tidak memiliki hak akses untuk masuk ke domain ini.');
        }

        return $next($request);
    }
}
