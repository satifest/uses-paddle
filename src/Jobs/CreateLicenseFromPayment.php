<?php

namespace Satifest\Paddle\Jobs;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Money\Currency;
use Money\Money;
use Satifest\Foundation\Actions\CreateLicense;
use Satifest\Foundation\Licensing;

class CreateLicenseFromPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Paddle webhook payloads.
     *
     * @var array
     */
    public $webhookPayload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $webhookPayload)
    {
        $this->webhookPayload = $webhookPayload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $payload = $this->webhookPayload;

        $passthrough = \json_decode($payload['passthrough'], true);
        $amount = new Money(($payload['sale_gross'] * 100), new Currency($payload['currency']));

        if (! \is_null($passthrough['license_plans'])) {
            $plans = \explode(',', $passthrough['license_plans']);
        }

        $licensing = Licensing::makePurchase(
            'cashier-paddle', $payload['checkout_id'], $amount, ($passthrough['license_allocation'] ?? 0)
        );

        if (! \is_null($passthrough['license_name'] ?? null)) {
            $licensing->alias($passthrough['license_name']);
        }

        if (! \is_null(($passthrough['license_ends_at'] ?? null))) {
            $licensing->supportedUntil(
                CarbonImmutable::createFromFormat('Y-m-d', $passthrough['license_ends_at'], 'UTC')->startOfDay()
            );
        }

        $action = new CreateLicense($passthrough['billable_id'], $passthrough['billable_type']);

        $action($licensing, ! empty($plans) ? $plans : '*');
    }
}
