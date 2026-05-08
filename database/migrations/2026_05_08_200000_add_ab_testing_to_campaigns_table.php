<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            if (! Schema::hasColumn('campaigns', 'ab_enabled')) {
                $table->boolean('ab_enabled')->default(false)->after('warmup_day');
            }
            if (! Schema::hasColumn('campaigns', 'ab_subject_b')) {
                $table->string('ab_subject_b')->nullable()->after('ab_enabled');
            }
            if (! Schema::hasColumn('campaigns', 'ab_body_b')) {
                $table->longText('ab_body_b')->nullable()->after('ab_subject_b');
            }
        });
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['ab_enabled', 'ab_subject_b', 'ab_body_b']);
        });
    }
};
