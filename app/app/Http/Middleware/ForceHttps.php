<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    /**
     * Handle an incoming request.
     * Forces HTTPS on production environment.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only force HTTPS in production, AND not for API routes, AND not for local docker network hosts
        $host = $request->header('host');
        $isInternal = str_contains($host, 'kecamatan-') || str_contains($host, 'dashboard-kecamatan');

        if ((config('app.env') === 'production' || config('app.force_https', false)) 
            && !$request->is('api/*') 
            && !$isInternal 
            && !$request->expectsJson()) {
            
            if (!$request->secure()) {
                // Redirect to HTTPS
                return redirect()->secure($request->getRequestUri());
            }
        }

        return $next($request);
    }
}
