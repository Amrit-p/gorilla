<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline browser hardening for a Blade + CDN stack.
 * Tighten Content-Security-Policy in production once script hosts are fixed.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Tailwind/jQuery/Leaflet + Google Maps allowlist (see Maps JS API CSP docs).
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' blob: https://cdn.tailwindcss.com https://cdn.jsdelivr.net https://code.jquery.com https://unpkg.com https://*.googleapis.com https://*.gstatic.com https://*.google.com",
            "style-src 'self' 'unsafe-inline' https://unpkg.com https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "img-src 'self' data: https: http: blob: https://*.googleapis.com https://*.gstatic.com https://*.google.com https://*.googleusercontent.com https://*.tile.openstreetmap.org https://*.openstreetmap.org",
            "font-src 'self' data: https: https://fonts.gstatic.com",
            "connect-src 'self' data: blob: https://api.mapbox.com https://*.tiles.mapbox.com https://unpkg.com https://cdn.jsdelivr.net https://*.tile.openstreetmap.org https://*.googleapis.com https://*.google.com https://*.gstatic.com",
            "frame-src 'self' https://*.google.com",
            "worker-src blob:",
            "frame-ancestors 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
