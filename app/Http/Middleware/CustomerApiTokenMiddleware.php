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
     * Strictly requires authorization token: Header "Authorization: Bearer <token>" or parameter "token" / "api_token"
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bodyJson = json_decode($request->getContent(), true) ?? [];
        $trimmedJson = [];
        if (is_array($bodyJson)) {
            foreach ($bodyJson as $key => $val) {
                $trimmedJson[trim($key)] = $val;
            }
        }

        // Strictly extract authorization token
        $token = $request->bearerToken()
            ?? $request->input('api_token')
            ?? $request->input('token')
            ?? $request->header('api_token')
            ?? $request->header('token')
            ?? $request->json('api_token')
            ?? $request->json('token')
            ?? ($trimmedJson['api_token'] ?? null)
            ?? ($trimmedJson['token'] ?? null)
            ?? ($trimmedJson['access_token'] ?? null);

        if (empty($token)) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required.',
            ], 401);
        }

        $customer = Customer::where('api_token', $token)->first();

        if (!$customer) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Invalid or expired authorization token.',
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
        $request->setUserResolver(fn() => $customer);
        auth()->guard('customer')->setUser($customer);

        return $next($request);
    }
}
