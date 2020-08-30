<?php

namespace Satifest\Paddle\Plans;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
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
        'id' => null,
        'name' => null,
        'plans' => null,
        'allocation' => 0,
        'supportInterval' => null,
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
        if (\is_null($this->attributes['name']) && \is_null($this->attributes['id'])) {
            throw new RuntimeException('Missing $productId or $productName');
        }

        $options = [
            'return_url' => $returnTo,
            'passthrough' => \array_filter([
                'license_name' => $licenseName,
                'license_plans' => $this->licensePlans(),
                'license_allocation' => $this->attributes['allocation'] ?? 0,
                'license_ends_at' => $this->licenseEndsAt(),
            ]),
        ];

        return ! \is_null($this->attributes['id'])
            ? $billable->chargeProduct($this->attributes['id'], $options)
            : $billable->charge($amount, $this->attributes['name'], $options);
    }

    /**
     * Get license plans.
     */
    public function licensePlans(): ?string
    {
        $plans = $this->attributes['plans'];

        return \is_array($plans) ? \implode(',', $plans) : $plans;
    }

    /**
     * Get license ends at.
     */
    public function licenseEndsAt(): ?string
    {
        if (\is_null($this->attributes['supportInterval'])) {
            return null;
        }

        return Carbon::today()->add($this->attributes['supportInterval'])->format('Y-m-d');
    }
}
