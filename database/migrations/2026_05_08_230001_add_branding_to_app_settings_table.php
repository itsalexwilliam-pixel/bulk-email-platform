<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('app_settings', 'unsubscribe_logo_url')) {
                $table->string('unsubscribe_logo_url')->nullable()->after('timezone');
            }
            if (! Schema::hasColumn('app_settings', 'unsubscribe_message')) {
                $table->text('unsubscribe_message')->nullable()->after('unsubscribe_logo_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropColumn(['unsubscribe_logo_url', 'unsubscribe_message']);
        });
    }
};
