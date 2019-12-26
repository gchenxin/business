<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Api\TestLib;

class TestProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->bind('test', function ($app){
            return new TestLib();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
