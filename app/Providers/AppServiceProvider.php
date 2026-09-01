<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use Exception;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        //
    }

    public function boot(): void
    {

        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);

        try {
            if (Schema::hasTable('admin_setting')) {
                $settings = Cache::rememberForever('adminSettings', function () {
                    return \App\Models\AdminSetting::first();
                });
                View::share('settings', $settings);
                View::share('adminSetting', $settings);
            }

            if (Schema::hasTable('header_menus')) {
                $headerMenus = Cache::rememberForever('headerMenus', function () {
                    return \App\Models\HeaderMenu::orderBy('position')->get();
                });
                View::share('headerMenus', $headerMenus);
            }

            if (Schema::hasTable('mail_settings')) {
                $mailSetting = Cache::rememberForever('mailSettings', function () {
                    return \App\Models\MailSetting::first();
                });
                View::share('mailSetting', $mailSetting);
            }
        } catch (\Exception $e) {
            // DB not ready yet, just skip
        }

        // Custom directive to check admin guard permissions
        Blade::if('admincan', function ($permission) {
            $admin = Auth::guard('admin')->user();
            return $admin && $admin->can($permission);
        });

        // Optional: custom directive for multiple permissions
        Blade::if('admincanany', function ($permissions) {
            $admin = Auth::guard('admin')->user();
            return $admin && $admin->hasAnyPermission($permissions);
        });
    }
}
