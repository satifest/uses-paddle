<?php

namespace Satifest\Paddle\View\Components;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use RuntimeException;
use Satifest\Foundation\Satifest;

class ProductButton extends Component
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
        string $product,
        string $returnTo,
        ?string $licenseName
    ) {
        $this->payLink = Satifest::catalogue()
            ->find($product, 'product')
            ->createPayLink(
                $billable, $returnTo, $licenseName
            );
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
