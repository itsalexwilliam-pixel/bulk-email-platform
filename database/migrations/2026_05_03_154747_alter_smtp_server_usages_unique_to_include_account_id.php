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
        Schema::table('smtp_server_usages', function (Blueprint $table) {
            $table->dropUnique('smtp_server_usages_unique_server_date');
            $table->unique(
                ['smtp_server_id', 'account_id', 'usage_date'],
                'smtp_server_usages_unique_server_account_date'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('smtp_server_usages', function (Blueprint $table) {
            $table->dropUnique('smtp_server_usages_unique_server_account_date');
            $table->unique(['smtp_server_id', 'usage_date'], 'smtp_server_usages_unique_server_date');
        });
    }
};
