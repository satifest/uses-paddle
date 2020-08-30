<?php

namespace Satifest\Paddle\View\Components;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

class SubscribeButton extends Component
{
    /**
     * Cashier Paddle Pay Link.
     *
     * @var string
     */
    public $payLink;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        Model $billable,
        int $amount,
        ?string $name = null,
        $plans = '*',
        int $allocation = 0,
        ?string $redirectTo = null,
        ?string $subscriptionName = null
    ) {
        $this->payLink = $billable->newSubscription($subscriptionName ?? 'default', $amount)
            ->returnTo($redirectTo ?? \route('home'))
            ->withMetadata(\array_filter([
                'license_name' => $name,
                'license_plans' => $plans,
                'license_allocation' => $allocation,
            ]))->create();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return \view('satifest-paddle::components.subscribe-button');
    }
}
