<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 50)->unique();
            $table->unsignedInteger('emails_per_day')->default(100);
            $table->unsignedInteger('campaigns_limit')->default(3);
            $table->timestamps();
        });

        DB::table('plans')->insert([
            [
                'name' => 'Free',
                'slug' => 'free',
                'emails_per_day' => 100,
                'campaigns_limit' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'emails_per_day' => 5000,
                'campaigns_limit' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'emails_per_day' => 100000,
                'campaigns_limit' => 10000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
