<?php

namespace Satifest\Paddle\Plans;

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
        'plans' => null,
        'allocation' => 0,
        'subscriptionName' => 'default',
        'multiple' => false,
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
        return $billable->newSubscription($this->getSubscriptionName(), $this->attributes['amount'])
            ->returnTo($returnTo)
            ->withMetadata(
                \collect([
                    'license_name' => $licenseName,
                    'license_plans' => $this->getLicensePlans(),
                    'license_allocation' => $this->attributes['allocation'] ?? 0,
                ])->filter()->all()
            )->create();
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
     * Get subscription name.
     */
    protected function getSubscriptionName(): string
    {
        $name = $this->subscriptionName ?? 'default';

        if ($this->attributes['multiple'] === true) {
            return \sprintf('%s:%s', $name, (string) Str::uuid());
        }

        return $name;
    }
}
