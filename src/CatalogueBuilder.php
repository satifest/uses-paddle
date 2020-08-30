<?php

namespace Satifest\Paddle;

use Illuminate\Support\Collection;
use RuntimeException;

class CatalogueBuilder
{
    /**
     * Plan variants.
     *
     * @var \Illuminate\Collection\Collection
     */
    protected $variants;

    /**
     * Construct a new Plan builder.
     */
    public function __construct()
    {
        $this->variants = new Collection();
    }

    /**
     * Add product/subscription.
     *
     * @return $this
     */
    public function add($variant)
    {
        if ($variant instanceof Plans\Product) {
            return $this->product($variant);
        } elseif ($variant instanceof Plans\Subscription) {
            return $this->subscription($variant);
        }

        throw new RuntimeException('Unable to handle given ['.\get_class($variant).']');
    }

    /**
     * Add product.
     *
     * @return $this
     */
    public function product(Plans\Product $product)
    {
        $this->variants->push([
            'uid' => $product->uid(),
            'type' => 'product',
            'plan' => $product,
        ]);

        return $this;
    }

    /**
     * Add subscription.
     *
     * @return $this
     */
    public function subscription(Plans\Subscription $subscription)
    {
        $this->variants->push([
            'uid' => $subscription->uid(),
            'type' => 'subscription',
            'plan' => $subscription,
        ]);

        return $this;
    }

    /**
     * Find variant.
     *
     * @return \Satifest\Paddle\Plans\Subscription|\Satifest\Foundation\Plans\Product|null
     */
    public function find(string $uid, ?string $type = null)
    {
        $variants = $this->variants->where('uid', $uid);

        if (! is_null($type)) {
            return $variants->where('type', $type)->first()['plan'] ?? null;
        }

        return $variants->first()['plan'] ?? null;
    }
}
