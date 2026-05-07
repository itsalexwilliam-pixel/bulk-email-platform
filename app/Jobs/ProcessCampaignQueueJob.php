<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class ProcessCampaignQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries = 1;

    public function __construct(
        public int $campaignId,
        public ?int $limit = null
    ) {
    }

    public function handle(): void
    {
        $lockKey = "campaign_queue_worker_running_{$this->campaignId}";

        if (!Cache::add($lockKey, 1, now()->addMinutes(2))) {
            return;
        }

        try {
            $options = [
                '--campaign_id' => $this->campaignId,
            ];

            if (!is_null($this->limit)) {
                $options['--limit'] = $this->limit;
            }

            Artisan::call('queue:work-mails', $options);
        } finally {
            Cache::forget($lockKey);
        }
    }
}
