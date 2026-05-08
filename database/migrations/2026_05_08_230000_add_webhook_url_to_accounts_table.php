<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('accounts', 'webhook_url')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->string('webhook_url')->nullable()->after('owner_user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('webhook_url');
        });
    }
};
