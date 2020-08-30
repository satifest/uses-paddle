<?php

namespace Satifest\Paddle\Tests\Feature\Jobs;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Satifest\Foundation\Testing\Factories\UserFactory;
use Satifest\Paddle\Jobs\CreateLicenseFromPayment;
use Satifest\Paddle\Tests\TestCase;

class CreateLicenseFromPaymentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_license_from_single_payment()
    {
        $checkoutId = Str::random(12);

        $user = UserFactory::new()->create();

        CreateLicenseFromPayment::dispatchNow([
            'checkout_id' => $checkoutId,
            'sale_gross' => 29.99,
            'currency' => 'USD',
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
            'uid' => $checkoutId,
            'type' => 'purchase',
            'amount' => '2999',
            'currency' => 'USD',
            'ends_at' => null,
            'allocation' => '5',
            'utilisation' => '0',
        ]);
    }

    /** @test */
    public function it_can_create_license_from_single_payment_with_custom_name()
    {
        $checkoutId = Str::random(12);

        $user = UserFactory::new()->create();

        CreateLicenseFromPayment::dispatchNow([
            'checkout_id' => $checkoutId,
            'sale_gross' => 29.99,
            'currency' => 'USD',
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
            'uid' => $checkoutId,
            'type' => 'purchase',
            'amount' => '2999',
            'currency' => 'USD',
            'ends_at' => null,
            'allocation' => '5',
            'utilisation' => '0',
        ]);
    }

    /** @test */
    public function it_can_create_license_from_single_payment_with_custom_ends_date()
    {
        $checkoutId = Str::random(12);
        $endsAt = Carbon::today()->addYear(1);

        $user = UserFactory::new()->create();

        CreateLicenseFromPayment::dispatchNow([
            'checkout_id' => $checkoutId,
            'sale_gross' => 29.99,
            'currency' => 'USD',
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
            'uid' => $checkoutId,
            'type' => 'purchase',
            'amount' => '2999',
            'currency' => 'USD',
            'ends_at' => $endsAt->toDatetimeString(),
            'allocation' => '5',
            'utilisation' => '0',
        ]);
    }
}
