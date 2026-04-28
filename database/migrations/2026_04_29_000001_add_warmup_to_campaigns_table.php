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
        Schema::table('campaigns', function (Blueprint $table) {
            $table->boolean('warmup_enabled')->default(false)->after('attachment_name');
            $table->unsignedTinyInteger('warmup_day')->default(1)->after('warmup_enabled');
            $table->timestamp('warmup_started_at')->nullable()->after('warmup_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'warmup_enabled',
                'warmup_day',
                'warmup_started_at',
            ]);
        });
    }
};
