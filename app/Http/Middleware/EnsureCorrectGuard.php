<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCorrectGuard
{
    // Centralized login and dashboard routes
    protected array $loginRoutes = [
        'web' => 'login',
        'admin' => 'admin.login.page',
    ];

    protected array $dashboardRoutes = [
        'web' => 'dashboard',
        'admin' => 'admin.dashboard',
    ];

    public function handle(Request $request, Closure $next, string $guard)
    {
        // If user is not logged in with the required guard
        if (!Auth::guard($guard)->check()) {
            // Logout from all other guards
            foreach (['web', 'admin'] as $otherGuard) {
                if ($otherGuard !== $guard && Auth::guard($otherGuard)->check()) {
                    Auth::guard($otherGuard)->logout();
                }
            }

            // Redirect to login route for the requested guard
            if (array_key_exists($guard, $this->loginRoutes) && route($this->loginRoutes[$guard], [], false)) {
                return redirect()->route($this->loginRoutes[$guard]);
            }
        }

        // If already logged in and try to access it own guard's login page, redirect to its correct logged in guards dashboard page 
        if (Auth::guard($guard)->check() && $request->routeIs($this->loginRoutes[$guard])) {
            return redirect()->route($this->dashboardRoutes[$guard]);
        }


        // Allow request to proceed
        return $next($request);
    }
}
