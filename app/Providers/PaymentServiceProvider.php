<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Payments\PaymentContext;
use App\Payments\PaymentStrategyInterface;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Register PaymentContext as a singleton
        $this->app->singleton(PaymentContext::class, function ($app) {
            return new PaymentContext();
        });

        // You can also bind specific strategies if needed
        // $this->app->bind('payment.cash', CashPaymentStrategy::class);
        // $this->app->bind('payment.credit_card', CreditCardPaymentStrategy::class);
        // etc.
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