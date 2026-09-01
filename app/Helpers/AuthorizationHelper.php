<?php

use Illuminate\Support\Facades\Gate;

if (!function_exists('authorize_any')) {
    function authorize_any($abilities)
    {
        // Get the authenticated user from the 'admin' guard
        $adminUser = auth('admin')->user();

        // Abort if no admin user is logged in
        if (!$adminUser) {
            abort(403, 'Unauthorized');
        }

        // Use Gate::forUser() to check abilities for the admin user
        if (is_array($abilities)) {
            if (!Gate::forUser($adminUser)->any($abilities)) {
                abort(403, 'Forbidden - You don\'t have sufficient permissions.');
            }
        } else {
            Gate::forUser($adminUser)->authorize($abilities);
        }
    }
}