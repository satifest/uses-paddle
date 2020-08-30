<?php

namespace Satifest\Paddle\Plans;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Fluent;
use RuntimeException;

class Product extends Fluent
{
    /**
     * All of the attributes set on the fluent instance.
     *
     * @var array
     */
    protected $attributes = [
        'uid' => null,
        'plans' => null,
        'allocation' => 0,
        'supportInterval' => null,
        'productId' => null,
        'productName' => null,
    ];

    /**
     * Construct a new subscription.
     *
     * @return static
     */
    public static function make(string $uid, int $amount)
    {
        return new static(\array_filter([
            'uid' => $uid,
            'amount' => $amount,
        ]));
    }

    /**
     * Product plan UID.
     */
    public function uid(): string
    {
        return $this->attributes['uid'];
    }

    /**
     * Set lifetime support.
     *
     * @return $this
     */
    public function lifetime()
    {
        return $this->supportInterval(null);
    }

    /**
     * Set support interval.
     */
    public function supportInterval(?CarbonInterval $interval)
    {
        $this->attributes['supportInterval'] = $interval;
        return $this;
    }

    /**
     * Generate pay link.
     */
    public function createPayLink(Model $billable, string $returnTo, ?string $licenseName = null): string
    {
        if (\is_null($this->attributes['productName']) && \is_null($this->attributes['productId'])) {
            throw new RuntimeException('Missing $productId or $productName');
        }

         $charge = ! \is_null($productId)
            ? $billable->chargeProduct($this->attributes['productId'])
            : $billable->charge($amount, $this->attributes['productName']);

        return $charge->returnTo($redirectTo ?? \route('home'))
            ->withMetadata(\array_filter([
                'license_name' => $licenseName,
                'license_plans' => $this->getLicensePlans(),
                'license_allocation' => $this->attributes['allocation'] ?? 0,
                'license_ends_at' => $this->getLicenseEndsAt(),
            ]))->create();
    }

    /**
     * Get license plans.
     */
    protected function getLicensePlans(): ?string
    {
        $plans = $this->attributes['plans'];

        return \is_array($plans) ? \implode(',', $plans) : $plans;
    }

    /**
     * Get license ends at.
     */
    protected function getLicenseEndsAt(): ?string
    {
        if (\is_null($this->attributes['supportInterval'])) {
            return null;
        }

        return Carbon::today()->add($this->attributes['supportInterval'])->format('Y-m-d');
    }
}
