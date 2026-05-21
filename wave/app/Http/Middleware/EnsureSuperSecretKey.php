<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
            return response()->json([
                'message' => 'nieautoryzowany',
            ], 401);
        }

        if (blank($expected)) {
            return response()->json([
                'message' => 'nieautoryzowany',
            ], 500);
        }

        if (! hash_equals((string) $expected, (string) $provided)) {
            return response()->json([
                'message' => 'nieautoryzowany',
            ], 403);
        }

        return $next($request);
    }
}
