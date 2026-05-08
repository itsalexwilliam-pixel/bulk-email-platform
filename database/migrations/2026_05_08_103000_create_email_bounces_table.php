<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_bounces', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('bounce_type', 20)->default('hard'); // hard | soft | complaint
            $table->string('bounce_subtype', 50)->nullable();
            $table->string('diagnostic', 255)->nullable();
            $table->string('source', 30)->default('ses'); // ses | manual
            $table->unsignedBigInteger('account_id')->nullable()->index();
            $table->unsignedBigInteger('email_queue_id')->nullable()->index();
            $table->timestamp('bounced_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_bounces');
    }
};
