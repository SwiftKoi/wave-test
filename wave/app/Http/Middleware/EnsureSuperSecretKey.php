<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class EnsureSuperSecretKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('services.super_secret_key');
        $provided = $request->header('X-SUPER-SECRET-KEY');

        if (blank($provided)) {
            throw new UnauthorizedHttpException('', 'Brak naglowka autoryzacyjnego.');
        }

        if (blank($expected)) {
            throw new HttpException(500, 'Brak konfiguracji klucza serwera.');
        }

        if (! hash_equals((string) $expected, (string) $provided)) {
            throw new AccessDeniedHttpException('Niepoprawny klucz autoryzacyjny.');
        }

        return $next($request);
    }
}
