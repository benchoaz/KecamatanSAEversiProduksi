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
        // Only force HTTPS in production
        if (config('app.env') === 'production' || config('app.force_https', false)) {
            if (!$request->secure()) {
                // Redirect to HTTPS
                return redirect()->secure($request->getRequestUri());
            }
        }

        return $next($request);
    }
}
