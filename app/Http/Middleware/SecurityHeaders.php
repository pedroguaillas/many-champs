<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SEGURIDAD: Middleware para agregar headers de seguridad HTTP (OWASP A05:2021)
 *
 * - X-Content-Type-Options: Previene MIME type sniffing
 * - X-Frame-Options: Previene clickjacking
 * - X-XSS-Protection: Protección XSS del navegador (legacy)
 * - Referrer-Policy: Limita información enviada en el header Referer
 * - Strict-Transport-Security: Fuerza HTTPS (HSTS)
 * - Permissions-Policy: Restringe APIs del navegador
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // HSTS: Solo activar en producción con HTTPS
        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
