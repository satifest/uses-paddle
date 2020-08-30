<?php

namespace Satifest\Paddle\Tests;

use Satifest\Foundation\Satifest;
use Satifest\Foundation\Testing\User;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        $this->afterApplicationRefreshed(function () {
            $this->loadLaravelMigrations();
        });

        parent::setUp();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        Satifest::setUserModel(User::class);
    }

    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            'Satifest\Foundation\SatifestServiceProvider',
            'Satifest\Paddle\PaddleServiceProvider',
        ];
    }
}
