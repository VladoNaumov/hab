<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\OrderCodeGenerator;
use App\Services\OrderService;
use App\Services\ResponseService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Регистрируем CartService
        $this->app->singleton(CartService::class, function ($app) {
            return new CartService();
        });

        // Регистрируем OrderService
        $this->app->singleton(OrderService::class, function ($app) {
            return new OrderService($app->make(CartService::class));
        });

        // Регистрируем ResponseService
        $this->app->singleton(ResponseService::class, function ($app) {
            return new ResponseService();
        });

        $this->app->singleton(OrderCodeGenerator::class, function () {
            return new OrderCodeGenerator();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
