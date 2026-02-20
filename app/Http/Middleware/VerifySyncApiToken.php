<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySyncApiToken
{
    public function handle(Request $request, Closure $next): JsonResponse|Response
    {
        $configuredToken = (string) config('services.sync_api.token', '');

        if ($configuredToken === '') {
            return response()->json([
                'success' => false,
                'message' => 'API token no configurado'
            ], 503);
        }

        $providedToken = (string) $request->header('X-SYNC-TOKEN', '');

        if ($providedToken === '' && $request->bearerToken()) {
            $providedToken = (string) $request->bearerToken();
        }

        if ($providedToken === '' || !hash_equals($configuredToken, $providedToken)) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 401);
        }

        return $next($request);
    }
}
