<?php

namespace Satifest\Paddle;

use Illuminate\Support\ServiceProvider;
use Laravel\Paddle\Cashier;
use Satifest\Foundation\Satifest;

class PaddleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        Cashier::ignoreRoutes();

        $this->app->singleton('satifest.paddle.catalogue', static function () {
            return new CatalogueBuilder();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Satifest::macro('catalogue', function () {
            return \app('satifest.paddle.catalogue');
        });

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'satifest-paddle');

        $this->loadRoutesFrom(__DIR__.'/../routes/webhook.php');
    }
}
