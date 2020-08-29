<?php

namespace Satifest\Paddle;

use Illuminate\Support\ServiceProvider;

class PaddleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // - enable webhook route for paddle
        // - add subscribed handler to generate Licensing::makeSubscription()
        // - add paid handler to generate Licensing::makePurchase()
        // - add subscribed failed/reject to revoke Licensing::makeSubscription()
        // - add blade component to make payment and assign plans
        // - add blade component to make subscription and assign plans.
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
