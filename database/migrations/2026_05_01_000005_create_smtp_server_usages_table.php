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
        Schema::create('smtp_server_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smtp_server_id')->constrained('smtp_servers')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->date('usage_date');
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('fail_count')->default(0);
            $table->timestamps();

            $table->unique(['smtp_server_id', 'usage_date'], 'smtp_server_usages_unique_server_date');
            $table->index(['account_id', 'usage_date'], 'smtp_server_usages_account_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smtp_server_usages');
    }
};
