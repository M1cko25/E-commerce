<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Redirect if authenticated
        RedirectIfAuthenticated::redirectUsing(function ($guard = null) {
            // Check the guard and redirect based on role
            $user = Auth::guard($guard)->user(); // Get the authenticated user
            if ($user && $user->role === 'admin') {
                return route('dashboard'); // Redirect admins to the dashboard
            }
            return route('customer.profile'); // Redirect customers to their profile
        });

        // Redirect unauthenticated users
        Authenticate::redirectUsing(function () {
            // Flash an error message
            Session::flash('error', 'You must be logged in to access this page.');

            // Redirect unauthenticated users to the appropriate login
            if (request()->is('admin/*')) {
                return route('admin.login'); // Admin routes redirect to admin login
            }
            return route('customer.login'); // Customer routes redirect to customer login
        });

        // Add support for current_password validation with custom guard
        Validator::extend('current_password', function ($attribute, $value, $parameters, $validator) {
            $guard = $parameters[0] ?? null;
            $user = $guard ? Auth::guard($guard)->user() : Auth::user();

            return Hash::check($value, $user->password);
        });

        // Add custom error message for current_password validation
        Validator::replacer('current_password', function ($message, $attribute, $rule, $parameters) {
            return 'The current password is incorrect.';
        });

        // Force HTTPS in production and configure URL correctly for verification links
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
            URL::forceRootUrl(config('app.url'));

            // Set the correct URL for generating signed URLs in production
            URL::createUrlUsing(function ($path) {
                return 'https://drm-hardware.com' . $path;
            });
        }
    }
}
