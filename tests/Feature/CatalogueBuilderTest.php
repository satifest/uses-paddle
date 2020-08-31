<?php

namespace Satifest\Paddle\Tests\Feature;

use Satifest\Foundation\Satifest;
use Satifest\Foundation\Testing\User;
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

        $builder->subscription($solo = Subscription::make('solo', 123457))
            ->subscription($pro = Subscription::make('pro', 123456));

        $this->assertSame($solo, $builder->find('solo'));
        $this->assertSame($pro, $builder->find('pro'));
    }

    /** @test */
    public function it_can_configure_products()
    {
        $builder = Satifest::catalogue();

        $builder->product($solo = Product::make('solo', 123457))
            ->product($pro = Product::make('pro', 123456));

        $this->assertSame($solo, $builder->find('solo'));
        $this->assertSame($pro, $builder->find('pro'));
    }

    /** @test */
    public function it_can_configure_products_and_subscriptions()
    {
        $builder = Satifest::catalogue();

        $builder->add($subscriptionSolo = Subscription::make('solo', 123457))
            ->add($subscriptionPro = Subscription::make('pro', 123456))
            ->add($productSolo = Product::make('solo', 123457))
            ->add($productPro = Product::make('pro', 123456));

        $this->assertSame($subscriptionSolo, $builder->find('solo', 'subscription'));
        $this->assertSame($subscriptionPro, $builder->find('pro', 'subscription'));
        $this->assertSame($productSolo, $builder->find('solo', 'product'));
        $this->assertSame($productPro, $builder->find('pro', 'product'));
    }

    /** @test */
    public function it_cant_configure_invalid_data()
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('Unable to handle given [Satifest\Foundation\Testing\User]');

        Satifest::catalogue()->add(new User());
    }
}
