<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->boolean('ab_enabled')->default(false)->after('warmup_day');
            $table->string('ab_subject_b')->nullable()->after('ab_enabled');
            $table->longText('ab_body_b')->nullable()->after('ab_subject_b');
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['ab_enabled', 'ab_subject_b', 'ab_body_b']);
        });
    }
};
