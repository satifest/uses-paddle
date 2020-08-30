<?php

use Satifest\Foundation\Http\Middleware\JsonMiddleware;
use Satifest\Foundation\Satifest;

Satifest::dashboardRoute('Satifest\Paddle\Http\Controllers')
    ->middleware([JsonMiddleware::class])
    ->group(static function (Router $router) {
        $router->prefix('webhook')
            ->group(static function (Router $router) {
                $router->post('paddle', 'WebhookController')->name('cashier.webhook');
            });
    });
