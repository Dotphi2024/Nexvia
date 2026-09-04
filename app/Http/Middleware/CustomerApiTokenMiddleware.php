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
        $bodyJson = json_decode($request->getContent(), true) ?? [];
        $trimmedJson = [];
        if (is_array($bodyJson)) {
            foreach ($bodyJson as $key => $val) {
                $trimmedJson[trim($key)] = $val;
            }
        }

        $userId = $request->input('id')
            ?? $request->input('user_id')
            ?? $request->input('customer_id')
            ?? ($trimmedJson['id'] ?? null)
            ?? ($trimmedJson['user_id'] ?? null)
            ?? ($trimmedJson['customer_id'] ?? null);

        $token = $request->bearerToken()
            ?? $request->input('token')
            ?? $request->header('token')
            ?? $request->json('token')
            ?? ($trimmedJson['token'] ?? null)
            ?? ($trimmedJson['refreshToken'] ?? null)
            ?? ($trimmedJson['access_token'] ?? null);

        $customer = null;

        if (!empty($userId)) {
            $customer = Customer::find($userId);
        } elseif (!empty($token)) {
            $customer = Customer::where('api_token', $token)->first();
        }

        if (empty($userId) && empty($token)) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. User ID (id) or Authorization Token is required.',
            ], 401);
        }

        if (!$customer) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Invalid User ID or token.',
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
