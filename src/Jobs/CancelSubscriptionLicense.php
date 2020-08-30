<?php

namespace Satifest\Paddle\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Satifest\Foundation\License;

class CancelSubscriptionLicense implements ShouldQueue
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

        $endsAt = Carbon::createFromFormat('Y-m-d', $payload['cancellation_effective_date'], 'UTC')->startOfDay();

        $license = License::query()
            ->where('provider', 'cashier-paddle')
            ->where('uid', $payload['subscription_id'])
            ->where('type', 'recurring')
            ->firstOrFail();

        $license->ends_at = $endsAt;
        $license->save();
    }
}
