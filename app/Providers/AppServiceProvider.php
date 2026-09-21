<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Custom Blade Directives for Simple Role Checking
        Blade::if('role', function (...$roles) {
            return auth()->check() && auth()->user()->hasRole($roles);
        });

        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->isAdmin();
        });

        Blade::if('manager', function () {
            return auth()->check() && auth()->user()->isManager();
        });

        Blade::if('cashier', function () {
            return auth()->check() && auth()->user()->isCashier();
        });
    }
}
