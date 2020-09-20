<?php

namespace kashem\licenseChecker;

use Illuminate\Support\ServiceProvider;

class licenseCheckerServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('kashem\licenseChecker\ProductVerifyController');
        $this->loadViewsFrom(__DIR__.'/views','licenseChecker');
    }
}