<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureAdminIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        // Check admin status (if you use status column like "active", "suspended")
        if ($admin && $admin->status !== 'active') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account is suspended. Please contact the administrator.',
                ], 403);
            }

            Auth::guard('admin')->logout();
            return redirect()->route('admin.login.page')
                ->with('error', 'Your account is suspended. Please contact the administrator.');
        }

        return $next($request);
    }
}