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
        Schema::table('smtp_servers', function (Blueprint $table) {
            if (! Schema::hasColumn('smtp_servers', 'daily_limit')) {
                $table->unsignedInteger('daily_limit')->nullable()->after('is_active');
            }

            if (! Schema::hasColumn('smtp_servers', 'priority')) {
                $table->unsignedInteger('priority')->nullable()->after('daily_limit');
            }
        });

        Schema::table('smtp_servers', function (Blueprint $table) {
            $table->unique(['account_id', 'name'], 'smtp_servers_account_name_unique');
            $table->unique(['account_id', 'host', 'username'], 'smtp_servers_account_host_username_unique');
            $table->index(['account_id', 'is_active', 'priority'], 'smtp_servers_account_active_priority_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('smtp_servers', function (Blueprint $table) {
            $table->dropIndex('smtp_servers_account_active_priority_idx');
            $table->dropUnique('smtp_servers_account_host_username_unique');
            $table->dropUnique('smtp_servers_account_name_unique');

            if (Schema::hasColumn('smtp_servers', 'priority')) {
                $table->dropColumn('priority');
            }

            if (Schema::hasColumn('smtp_servers', 'daily_limit')) {
                $table->dropColumn('daily_limit');
            }
        });
    }
};
