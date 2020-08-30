<?php

namespace Satifest\Paddle\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Satifest\Foundation\Satifest;
use Satifest\Paddle\Plans\Product;
use Satifest\Paddle\Plans\Subscription;
use Satifest\Paddle\Tests\TestCase;

class CatalogueBuilderTest extends TestCase
{
    /** @test */
    public function it_can_get_catalogue_builder()
    {
        $builder = Satifest::catalogue();

        $this->assertSame(Satifest::catalogue(), $builder);
    }

    /** @test */
    public function it_can_configure_subscriptions()
    {
        $builder = Satifest::catalogue();

        $builder->subscription($solo = Subscription::make('solo', 4900))
            ->subscription($pro = Subscription::make('pro', 14900));

        $this->assertSame($solo, $builder->find('solo'));
        $this->assertSame($pro, $builder->find('pro'));
    }

    /** @test */
    public function it_can_configure_products()
    {
        $builder = Satifest::catalogue();

        $builder->product($solo = Product::make('solo', 4900))
            ->product($pro = Product::make('pro', 14900));

        $this->assertSame($solo, $builder->find('solo'));
        $this->assertSame($pro, $builder->find('pro'));
    }

    /** @test */
    public function it_can_configure_products_and_subscriptions()
    {
        $builder = Satifest::catalogue();

        $builder->add($subscriptionSolo = Subscription::make('solo', 4900))
            ->add($subscriptionPro = Subscription::make('pro', 14900))
            ->add($productSolo = Product::make('solo', 4900))
            ->add($productPro = Product::make('pro', 14900));

        $this->assertSame($subscriptionSolo, $builder->find('solo', 'subscription'));
        $this->assertSame($subscriptionPro, $builder->find('pro', 'subscription'));
        $this->assertSame($productSolo, $builder->find('solo', 'product'));
        $this->assertSame($productPro, $builder->find('pro', 'product'));
    }
}
