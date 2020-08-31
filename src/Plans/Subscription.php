<?php

namespace Satifest\Paddle\Plans;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

class Subscription extends Fluent
{
    /**
     * All of the attributes set on the fluent instance.
     *
     * @var array
     */
    protected $attributes = [
        'uid' => null,
        'paddleId' => null,
        'name' => 'default',
        'plans' => null,
        'allocation' => 0,
        'multiple' => false,
    ];

    /**
     * Construct a new subscription.
     *
     * @return static
     */
    public static function make(string $uid, int $paddleId)
    {
        return new static(\array_filter([
            'uid' => $uid,
            'paddleId' => $paddleId,
        ]));
    }

    /**
     * Subscription plan UID.
     */
    public function uid(): string
    {
        return $this->attributes['uid'];
    }

    /**
     * Generate pay link.
     */
    public function createPayLink(Model $billable, string $returnTo, ?string $licenseName = null): string
    {
        return $billable->newSubscription($this->subscriptionName(), $this->attributes['paddleId'])
            ->returnTo($returnTo)
            ->withMetadata(
                \collect([
                    'license_name' => $licenseName,
                    'license_plans' => $this->licensePlans(),
                    'license_allocation' => $this->attributes['allocation'] ?? 0,
                ])->filter()->all()
            )->create();
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
     * Get subscription name.
     */
    public function subscriptionName(): string
    {
        $name = $this->attributes['name'] ?? 'default';

        if ($this->attributes['multiple'] === true) {
            return \sprintf('%s:%s', $name, (string) Str::uuid());
        }

        return $name;
    }
}
