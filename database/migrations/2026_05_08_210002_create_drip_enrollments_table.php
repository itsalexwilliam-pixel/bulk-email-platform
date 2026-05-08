<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drip_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('drip_campaign_id')->index();
            $table->unsignedBigInteger('contact_id')->index();
            $table->unsignedBigInteger('account_id')->index();
            $table->unsignedInteger('current_step')->default(1); // which step to send next
            $table->timestamp('next_send_at')->nullable();        // when to send the next step
            $table->string('status')->default('active');          // active, completed, unsubscribed
            $table->timestamps();

            $table->unique(['drip_campaign_id', 'contact_id']); // one enrollment per contact per drip
            $table->foreign('drip_campaign_id')->references('id')->on('drip_campaigns')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drip_enrollments');
    }
};
