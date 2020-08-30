<?php

namespace Satifest\Paddle\Tests\Feature\Jobs;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Satifest\Foundation\Testing\Factories\LicenseFactory;
use Satifest\Foundation\Testing\Factories\UserFactory;
use Satifest\Paddle\Jobs\CancelSubscriptionLicense;
use Satifest\Paddle\Tests\TestCase;

class CancelSubscriptionLicenseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_cancel_subscription_for_a_license()
    {
        $subscriptionId = Str::random(12);

        $user = UserFactory::new()->create();

        $license = LicenseFactory::new()->create([
            'licensable_id' => $user->getKey(),
            'licensable_type' => $user->getMorphClass(),
            'type' => 'recurring',
            'provider' => 'cashier-paddle',
            'uid' => $subscriptionId,
        ]);

        CancelSubscriptionLicense::dispatchNow([
            'cancellation_effective_date' => $endsAt = Carbon::today()->addDays(7)->format('Y-m-d'),
            'subscription_id' => $subscriptionId,
        ]);

        $license->refresh();

        $this->assertSame($endsAt, $license->ends_at->format('Y-m-d'));
    }

    /** @test */
    public function it_cant_cancel_subscription_for_an_invalid_license()
    {
        $this->expectException('Illuminate\Database\Eloquent\ModelNotFoundException');
        $this->expectExceptionMessage('No query results for model [Satifest\Foundation\License]');

        $subscriptionId = Str::random(12);

        $user = UserFactory::new()->create();

        $license = LicenseFactory::new()->create([
            'licensable_id' => $user->getKey(),
            'licensable_type' => $user->getMorphClass(),
            'type' => 'recurring',
            'provider' => 'cashier-paddle',
            'uid' => 'secret',
        ]);

        CancelSubscriptionLicense::dispatchNow([
            'cancellation_effective_date' => $endsAt = Carbon::today()->addDays(7)->format('Y-m-d'),
            'subscription_id' => $subscriptionId,
        ]);
    }
}
