<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Driver;
use Symfony\Component\HttpFoundation\Response;

class DriverApiTokenMiddleware
{
    /**
     * Handle an incoming request for protected Driver APIs.
     *
     * Expects: Header "Authorization: Bearer <token>" or parameter "token"
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken() ?? $request->input('token') ?? $request->header('token');

        if (empty($token)) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Authorization token is required in headers (Bearer <token>).',
            ], 401);
        }

        $driver = Driver::where('api_token', $token)->first();

        if (!$driver) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Invalid or expired token.',
            ], 401);
        }

        if ($driver->status === 'inactive') {
            return response()->json([
                'status'  => false,
                'message' => 'Your account is inactive. Please contact support.',
            ], 403);
        }

        // Attach authenticated driver to request
        $request->attributes->set('authenticated_driver', $driver);

        return $next($request);
    }
}
