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
        if (! Schema::hasColumn('email_queue', 'body_snapshot')) {
            Schema::table('email_queue', function (Blueprint $table) {
                $table->longText('body_snapshot')->nullable()->after('body');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_queue', function (Blueprint $table) {
            $table->dropColumn('body_snapshot');
        });
    }
};
