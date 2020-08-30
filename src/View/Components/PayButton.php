<?php

namespace Satifest\Paddle\View\Components;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use RuntimeException;

class PayButton extends Component
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
        ?string $productName = null,
        ?string $productId = null,
        ?string $name = null,
        $plans = '*',
        int $allocation = 0,
        ?CarbonInterval $endsAfter,
        ?string $redirectTo = null
    ) {
        if (\is_null($productName) && \is_null($productId)) {
            throw new RuntimeException('Missing $productId or $productName');
        }

        $charge ! \is_null($productId)
            ? $billable->chargeProduct($productId)
            : $billable->charge($amount, $productName);

        $charge->returnTo($redirectTo ?? \route('home'))
            ->withMetadata(\array_filter([
                'license_name' => $name,
                'license_plans' => $plans,
                'license_allocation' => $allocation,
                'license_ends_at' => ! \is_null($endsAfter) ? Carbon::today()->add($endsAfter)->format('Y-m-d') : null,
            ]));

        $this->payLink = $charge->create();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return \view('satifest-paddle::components.pay-button');
    }
}
