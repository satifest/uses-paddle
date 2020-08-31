<?php

namespace Satifest\Paddle\Tests\Feature\Plans;

use Carbon\CarbonInterval;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Satifest\Paddle\Plans\Product;
use Satifest\Paddle\Tests\Factories\UserFactory;
use Satifest\Paddle\Tests\TestCase;
use Spatie\TestTime\TestTime;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_proper_signature()
    {
        $product = Product::make('solo', 4900);

        $this->assertSame('solo', $product->uid());
        $this->assertSame(4900, $product->amount);
        $this->assertSame(0, $product->allocation);
        $this->assertNull($product->supportInterval);
        $this->assertNull($product->name);
        $this->assertNull($product->id);
        $this->assertNull($product->licensePlans());
        $this->assertNull($product->licenseEndsAt());
    }

    /** @test */
    public function it_can_has_plans()
    {
        $product = Product::make('solo', 4900)->plans([1, 2, 3]);

        $this->assertSame('1,2,3', $product->licensePlans());
    }

    /** @test */
    public function it_can_all_plans()
    {
        $product = Product::make('solo', 4900)->plans('*');

        $this->assertSame('*', $product->licensePlans());
    }

    /** @test */
    public function it_can_has_limited_support_interval()
    {
        TestTime::freeze('Y-m-d', '2020-08-01');

        $product = Product::make('solo', 4900)->supportInterval(CarbonInterval::month(1));

        $this->assertSame('2020-09-01', $product->licenseEndsAt());
    }

    /** @test */
    public function it_can_has_unlimited_support_interval()
    {
        TestTime::freeze('Y-m-d', '2020-08-01');

        $product = Product::make('solo', 4900)->lifetime();

        $this->assertNull($product->licenseEndsAt());
    }

    /** @test */
    public function it_can_create_pay_link()
    {
        Http::fake(function ($request) {
            return Http::response(\json_encode(['success' => true, 'response' => ['url' => 'payment_link']]), 200);
        });

        $user = UserFactory::new()->create();

        $product = Product::make('solo', 4900)
            ->plans('*')
            ->allocation(5)
            ->paddleId('123456');

        $payLink = $product->createPayLink($user, 'home', 'Demo License');

        $this->assertSame('payment_link', $payLink);

        Http::assertSent(function ($request) use ($user) {
            return $request->url() == 'https://vendors.paddle.com/api/2.0/product/generate_pay_link'
                && $request['product_id'] == '123456'
                && $request['customer_email'] == $user->email
                && $request['passthrough'] == '{"license_name":"Demo License","license_plans":"*","license_allocation":5,"billable_id":'.$user->getKey().',"billable_type":"users"}';
        });
    }

    /** @test */
    public function it_cant_create_pay_link_without_product_name_or_id()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Missing $paddleId or $productName');

        $user = UserFactory::new()->create();

        $product = Product::make('solo', 4900)->lifetime();

        $product->createPayLink($user, 'home', 'Demo License');
    }
}
