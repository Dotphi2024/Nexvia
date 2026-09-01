<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Customer;
use Symfony\Component\HttpFoundation\Response;

class CustomerApiTokenMiddleware
{
    /**
     * Handle an incoming request for protected Customer APIs.
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

        $customer = Customer::where('api_token', $token)->first();

        if (!$customer) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Invalid or expired token.',
            ], 401);
        }

        if ($customer->status !== 'active') {
            return response()->json([
                'status'  => false,
                'message' => 'Your account is inactive. Please contact support.',
            ], 403);
        }

        // Attach authenticated customer to request
        $request->attributes->set('authenticated_customer', $customer);

        return $next($request);
    }
}
