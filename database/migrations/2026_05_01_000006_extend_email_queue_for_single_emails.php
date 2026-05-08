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
        if (! Schema::hasColumn('email_queue', 'type')) {
            Schema::table('email_queue', function (Blueprint $table) {
                $table->enum('type', ['campaign', 'single'])->default('campaign')->after('email');
                $table->foreignId('smtp_server_id')->nullable()->after('contact_id')->constrained('smtp_servers')->nullOnDelete();
                $table->string('subject')->nullable()->after('type');
                $table->longText('body')->nullable()->after('subject');
                $table->string('from_email')->nullable()->after('body');
                $table->string('from_name')->nullable()->after('from_email');
                $table->json('attachments')->nullable()->after('from_name');

                $table->index(['type', 'created_at'], 'email_queue_type_created_at_idx');
                $table->index('smtp_server_id', 'email_queue_smtp_server_id_idx');
            });
        }

        Schema::table('email_queue', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropForeign(['contact_id']);
            $table->dropUnique(['campaign_id', 'contact_id']);
        });

        Schema::table('email_queue', function (Blueprint $table) {
            $table->foreignId('campaign_id')->nullable()->change();
            $table->foreignId('contact_id')->nullable()->change();
        });

        Schema::table('email_queue', function (Blueprint $table) {
            $table->foreign('campaign_id')->references('id')->on('campaigns')->cascadeOnDelete();
            $table->foreign('contact_id')->references('id')->on('contacts')->cascadeOnDelete();
            $table->unique(['campaign_id', 'contact_id'], 'email_queue_campaign_contact_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_queue', function (Blueprint $table) {
            $table->dropUnique('email_queue_campaign_contact_unique');
            $table->dropForeign(['campaign_id']);
            $table->dropForeign(['contact_id']);
        });

        Schema::table('email_queue', function (Blueprint $table) {
            $table->foreignId('campaign_id')->nullable(false)->change();
            $table->foreignId('contact_id')->nullable(false)->change();
        });

        Schema::table('email_queue', function (Blueprint $table) {
            $table->foreign('campaign_id')->references('id')->on('campaigns')->cascadeOnDelete();
            $table->foreign('contact_id')->references('id')->on('contacts')->cascadeOnDelete();
            $table->unique(['campaign_id', 'contact_id']);
        });

        Schema::table('email_queue', function (Blueprint $table) {
            $table->dropIndex('email_queue_type_created_at_idx');
            $table->dropIndex('email_queue_smtp_server_id_idx');
            $table->dropConstrainedForeignId('smtp_server_id');
            $table->dropColumn([
                'type',
                'subject',
                'body',
                'from_email',
                'from_name',
                'attachments',
            ]);
        });
    }
};
