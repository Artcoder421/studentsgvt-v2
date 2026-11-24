<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->timestamp('ended_at')->nullable()->after('voting_ends_at');
            $table->foreignId('ended_by')->nullable()->after('ended_at')->constrained('users')->nullOnDelete();
        });

        Schema::table('timer_announcements', function (Blueprint $table) {
            $table->string('event_when', 140)->nullable()->after('subtitle');
            $table->string('event_where', 180)->nullable()->after('event_when');
            $table->text('planned_activities')->nullable()->after('event_where');
            $table->string('account_number', 180)->nullable()->after('planned_activities');
            $table->string('contact_info', 180)->nullable()->after('account_number');
        });

        Schema::table('charity_campaigns', function (Blueprint $table) {
            $table->string('event_when', 140)->nullable()->after('beneficiary');
            $table->string('event_where', 180)->nullable()->after('event_when');
            $table->text('planned_activities')->nullable()->after('event_where');
            $table->string('account_number', 180)->nullable()->after('planned_activities');
            $table->string('contact_info', 180)->nullable()->after('account_number');
        });
    }

    public function down(): void
    {
        Schema::table('charity_campaigns', function (Blueprint $table) {
            $table->dropColumn(['event_when', 'event_where', 'planned_activities', 'account_number', 'contact_info']);
        });

        Schema::table('timer_announcements', function (Blueprint $table) {
            $table->dropColumn(['event_when', 'event_where', 'planned_activities', 'account_number', 'contact_info']);
        });

        Schema::table('elections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ended_by');
            $table->dropColumn('ended_at');
        });
    }
};
