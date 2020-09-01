<?php

namespace Satifest\Paddle\Tests\Feature\Plans;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Satifest\Paddle\Plans\Subscription;
use Satifest\Paddle\Tests\Factories\UserFactory;
use Satifest\Paddle\Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_proper_signature()
    {
        $subscription = Subscription::make('solo', 123456);

        $this->assertSame('solo', $subscription->uid());
        $this->assertSame(123456, $subscription->paddleId);
        $this->assertSame(0, $subscription->allocation);
        $this->assertNull($subscription->supportInterval);
        $this->assertSame('default', $subscription->name);
        $this->assertFalse($subscription->multiple);
        $this->assertNull($subscription->licensePlans());
        $this->assertSame('default', $subscription->subscriptionName());
    }

    /** @test */
    public function it_can_generate_same_subscription_name_for_none_multiple()
    {
        $subscription = Subscription::make('solo', 123456);

        $this->assertEquals($subscription->subscriptionName(), $subscription->subscriptionName());
    }

    /** @test */
    public function it_can_generate_unique_subscription_name_for_multiple()
    {
        $subscription = Subscription::make('solo', 123456)->multiple();

        $this->assertNotEquals($subscription->subscriptionName(), $subscription->subscriptionName());
    }

    /** @test */
    public function it_can_create_pay_link()
    {
        Http::fake(function ($request) {
            return Http::response(\json_encode(['success' => true, 'response' => ['url' => 'payment_link']]), 200);
        });

        $user = UserFactory::new()->create();

        $subscription = Subscription::make('solo', 123456)
            ->plans('*')
            ->allocation(5)
            ->id('123456');

        $payLink = $subscription->createPayLink($user, 'home', 'Demo License');

        $this->assertSame('payment_link', $payLink);

        Http::assertSent(function ($request) use ($user) {
            return $request->url() == 'https://vendors.paddle.com/api/2.0/product/generate_pay_link'
                && $request['product_id'] == 123456
                && $request['customer_email'] == $user->email
                && $request['passthrough'] == '{"license_name":"Demo License","license_plans":"*","license_allocation":5,"subscription_name":"default","billable_id":'.$user->getKey().',"billable_type":"users"}'
                && in_array($request['return_url'], ['home?checkout=%7Bcheckout_hash%7D', 'home?checkout={checkout_hash}']);
        });
    }
}
