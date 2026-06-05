<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ResultadosService;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ResultadosService::class, function ($app) {
            return new ResultadosService();
        });
    }

    public function boot()
    {
        //
    }
}