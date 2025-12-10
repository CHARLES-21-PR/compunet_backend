<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('shop', function ($app) {
            return new \App\Services\ShopManager(
                $app->make(\App\Services\CartService::class),
                $app->make(\App\Services\CheckoutService::class),
                $app->make(\App\Services\GreenterService::class),
                $app->make(\App\Services\InvoiceService::class),
                $app->make(\App\Services\InventoryService::class),
                $app->make(\App\Services\PaymentService::class),
                $app->make(\App\Services\ShippingService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
