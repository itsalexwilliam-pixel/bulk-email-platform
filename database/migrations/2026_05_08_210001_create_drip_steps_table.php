<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drip_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('drip_campaign_id')->index();
            $table->unsignedInteger('position')->default(1); // order: 1, 2, 3...
            $table->string('subject');
            $table->longText('body');
            $table->unsignedInteger('delay_days')->default(0); // days after previous step (0 = immediate for step 1)
            $table->timestamps();

            $table->foreign('drip_campaign_id')->references('id')->on('drip_campaigns')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drip_steps');
    }
};
