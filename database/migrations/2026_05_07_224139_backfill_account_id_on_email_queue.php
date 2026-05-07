<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill account_id on email_queue rows where it is missing (0 or null),
     * using the account_id from the related campaign.
     */
    public function up(): void
    {
        // SQLite-compatible: update using a subquery
        DB::statement('
            UPDATE email_queue
            SET account_id = (
                SELECT campaigns.account_id
                FROM campaigns
                WHERE campaigns.id = email_queue.campaign_id
                  AND campaigns.account_id IS NOT NULL
                  AND campaigns.account_id != 0
                LIMIT 1
            )
            WHERE (account_id IS NULL OR account_id = 0)
              AND campaign_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        // Not reversible
    }
};
