<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('category', 30)->default('academic'); // academic, social, sports, charity, governance, holiday
            $table->string('venue_name');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('event_rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_event_id')->constrained('campus_events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('going'); // going, interested, not_going
            $table->timestamps();
            $table->unique(['campus_event_id', 'user_id']);
        });

        Schema::create('sports_activities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sport_type', 30)->default('football'); // football, basketball, volleyball, athletics, netball, chess, other
            $table->string('captain_name')->nullable();
            $table->string('schedule');
            $table->string('venue');
            $table->text('description')->nullable();
            $table->string('upcoming_match')->nullable();
            $table->timestamp('match_date')->nullable();
            $table->string('last_result')->nullable();
            $table->boolean('is_open_for_joining')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sports_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sports_activity_id')->constrained('sports_activities')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('position_or_skill')->nullable();
            $table->string('status', 20)->default('pending'); // pending, approved
            $table->timestamps();
            $table->unique(['sports_activity_id', 'user_id']);
        });

        Schema::create('charity_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('beneficiary');
            $table->text('description');
            $table->unsignedBigInteger('target_amount')->default(1000000);
            $table->unsignedBigInteger('raised_amount')->default(0);
            $table->timestamp('ends_at')->nullable();
            $table->text('payment_instructions')->nullable();
            $table->string('image')->nullable();
            $table->string('status', 20)->default('active'); // active, completed, paused
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('charity_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('charity_campaign_id')->constrained('charity_campaigns')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('donor_name');
            $table->unsignedBigInteger('amount');
            $table->string('payment_reference')->nullable();
            $table->string('message')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('status', 20)->default('pledged'); // pledged, verified
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('charity_contributions');
        Schema::dropIfExists('charity_campaigns');
        Schema::dropIfExists('sports_registrations');
        Schema::dropIfExists('sports_activities');
        Schema::dropIfExists('event_rsvps');
        Schema::dropIfExists('campus_events');
    }
};
