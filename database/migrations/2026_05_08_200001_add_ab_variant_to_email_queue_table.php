<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('email_queue', 'ab_variant')) {
            Schema::table('email_queue', function (Blueprint $table) {
                $table->char('ab_variant', 1)->nullable()->after('type'); // 'a' or 'b'
            });
        }
    }

    public function down(): void
    {
        Schema::table('email_queue', function (Blueprint $table) {
            $table->dropColumn('ab_variant');
        });
    }
};
