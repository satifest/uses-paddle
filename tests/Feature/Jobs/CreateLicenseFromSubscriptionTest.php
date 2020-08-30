<?php

namespace Satifest\Paddle\Tests\Feature\Jobs;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Satifest\Foundation\Testing\Factories\UserFactory;
use Satifest\Paddle\Jobs\CreateLicenseFromSubscription;
use Satifest\Paddle\Tests\TestCase;

class CreateLicenseFromSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_license_from_subscription()
    {
        $subscriptionId = Str::random(12);
        $nextBillingAt = Carbon::today()->addYear(1);

        $user = UserFactory::new()->create();

        CreateLicenseFromSubscription::dispatchNow([
            'subscription_id' => $subscriptionId,
            'sale_gross' => 29.99,
            'currency' => 'USD',
            'next_bill_date' => $nextBillingAt->format('Y-m-d'),
            'passthrough' => \json_encode([
                'billable_id' => $user->getKey(),
                'billable_type' => $user->getMorphClass(),
                'license_allocation' => 5,
                'license_plans' => '*',
            ]),
        ]);

        $this->assertDatabaseHas('sf_licenses', [
            'licensable_id' => "{$user->getKey()}",
            'licensable_type' => $user->getMorphClass(),
            'name' => null,
            'provider' => 'cashier-paddle',
            'uid' => $subscriptionId,
            'type' => 'recurring',
            'amount' => '2999',
            'currency' => 'USD',
            'ends_at' => $nextBillingAt->toDatetimeString(),
            'allocation' => '5',
            'utilisation' => '0',
        ]);
    }

    /** @test */
    public function it_can_create_license_from_subscription_with_custom_name()
    {
        $subscriptionId = Str::random(12);
        $nextBillingAt = Carbon::today()->addYear(1);

        $user = UserFactory::new()->create();

        CreateLicenseFromSubscription::dispatchNow([
            'subscription_id' => $subscriptionId,
            'sale_gross' => 29.99,
            'currency' => 'USD',
            'next_bill_date' => $nextBillingAt->format('Y-m-d'),
            'passthrough' => \json_encode([
                'billable_id' => $user->getKey(),
                'billable_type' => $user->getMorphClass(),
                'license_name' => 'Satifest Demo',
                'license_allocation' => 5,
                'license_plans' => '*',
            ]),
        ]);

        $this->assertDatabaseHas('sf_licenses', [
            'licensable_id' => "{$user->getKey()}",
            'licensable_type' => $user->getMorphClass(),
            'name' => 'Satifest Demo',
            'provider' => 'cashier-paddle',
            'uid' => $subscriptionId,
            'type' => 'recurring',
            'amount' => '2999',
            'currency' => 'USD',
            'ends_at' => $nextBillingAt->toDatetimeString(),
            'allocation' => '5',
            'utilisation' => '0',
        ]);
    }

    /** @test */
    public function it_can_create_license_from_subscription_but_cant_use_custom_ends_at()
    {
        $subscriptionId = Str::random(12);
        $nextBillingAt = Carbon::today()->addYear(1);
        $endsAt = Carbon::today()->addMonths(6);

        $user = UserFactory::new()->create();

        CreateLicenseFromSubscription::dispatchNow([
            'subscription_id' => $subscriptionId,
            'sale_gross' => 29.99,
            'currency' => 'USD',
            'next_bill_date' => $nextBillingAt->format('Y-m-d'),
            'passthrough' => \json_encode([
                'billable_id' => $user->getKey(),
                'billable_type' => $user->getMorphClass(),
                'license_allocation' => 5,
                'license_plans' => '*',
                'license_ends_at' => $endsAt->format('Y-m-d'),
            ]),
        ]);

        $this->assertDatabaseHas('sf_licenses', [
            'licensable_id' => "{$user->getKey()}",
            'licensable_type' => $user->getMorphClass(),
            'name' => null,
            'provider' => 'cashier-paddle',
            'uid' => $subscriptionId,
            'type' => 'recurring',
            'amount' => '2999',
            'currency' => 'USD',
            'ends_at' => $nextBillingAt->toDatetimeString(),
            'allocation' => '5',
            'utilisation' => '0',
        ]);
    }
}
