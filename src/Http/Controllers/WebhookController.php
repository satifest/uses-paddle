<?php

namespace Satifest\Paddle\Http\Controllers;

use Laravel\Paddle\Http\Controllers\WebhookController as CashierController;
use Satifest\Foundation\License;
use Satifest\Paddle\Jobs\CancelSubscriptionLicense;
use Satifest\Paddle\Jobs\CreateLicenseFromPayment;
use Satifest\Paddle\Jobs\CreateLicenseFromSubscription;
use Satifest\Paddle\Jobs\CreateOrUpdateLicenseFromSubscriptionPayment;

class WebhookController extends CashierController
{
    /**
     * Handle one-time payment succeeded.
     *
     * @param  array  $payload
     *
     * @return void
     */
    protected function handlePaymentSucceeded(array $payload)
    {
        parent::handlePaymentSucceeded($payload);

        CreateLicenseFromPayment::dispatch($payload);
    }

    /**
     * Handle subscription payment succeeded.
     *
     * @param  array  $payload
     *
     * @return void
     */
    protected function handleSubscriptionPaymentSucceeded(array $payload)
    {
        parent::handleSubscriptionPaymentSucceeded($payload);

        CreateOrUpdateLicenseFromSubscriptionPayment::dispatch($payload);
    }

    /**
     * Handle subscription created.
     *
     * @param  array  $payload
     *
     * @return void
     */
    protected function handleSubscriptionCreated(array $payload)
    {
        parent::handleSubscriptionCreated($payload);

        CreateLicenseFromSubscription::dispatch($payload);
    }

    /**
     * Handle subscription updated.
     *
     * @param  array  $payload
     *
     * @return void
     */
    protected function handleSubscriptionUpdated(array $payload)
    {
        parent::handleSubscriptionUpdated($payload);

        // Update license information for subscription.
        // - status
        // - quantity
        // - paused
    }

    /**
     * Handle subscription cancelled.
     *
     * @param  array  $payload
     *
     * @return void
     */
    protected function handleSubscriptionCancelled(array $payload)
    {
        parent::handleSubscriptionCancelled($payload);

        CancelSubscriptionLicense::dispatch($payload);
    }
}
