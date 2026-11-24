<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 30)->unique();
            $table->string('building');
            $table->unsignedInteger('capacity')->default(50);
            $table->string('category', 30)->default('lecture_hall'); // auditorium, lecture_hall, lab, sports, meeting, hangar
            $table->text('amenities')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('status', 20)->default('available'); // available, booked, maintenance
            $table->timestamps();
        });

        Schema::create('venue_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained('venues')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('event_title');
            $table->string('organization_or_course');
            $table->date('booking_date');
            $table->string('start_time', 10);
            $table->string('end_time', 10);
            $table->unsignedInteger('expected_attendees')->default(30);
            $table->text('purpose');
            $table->string('status', 20)->default('pending'); // pending, approved, declined
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type', 30)->default('on_campus_hostel'); // on_campus_hostel, verified_off_campus, shared_apartment
            $table->string('gender_policy', 20)->default('mixed'); // mixed, male, female
            $table->string('location');
            $table->string('distance_from_campus')->nullable();
            $table->unsignedBigInteger('price_per_semester')->default(0);
            $table->unsignedInteger('available_rooms')->default(0);
            $table->unsignedInteger('total_rooms')->default(0);
            $table->text('amenities')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('accommodation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accommodation_id')->constrained('accommodations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('preferred_room_type', 60);
            $table->string('move_in_semester', 60);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending'); // pending, approved, waitlisted, declined
            $table->text('admin_response')->nullable();
            $table->timestamps();
        });

        Schema::create('help_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 30)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('category', 30)->default('academic'); // academic, finance_heslb, accommodation, welfare, it_support, grievance, other
            $table->string('subject');
            $table->text('description');
            $table->string('priority', 20)->default('normal'); // low, normal, high, urgent
            $table->string('status', 20)->default('open')->index(); // open, in_progress, resolved, closed
            $table->boolean('is_anonymous')->default(false);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('help_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_ticket_id')->constrained('help_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('message');
            $table->boolean('is_staff_reply')->default(false);
            $table->timestamps();
        });

        Schema::create('lost_found_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 15)->default('found'); // lost, found
            $table->string('item_name');
            $table->string('category', 30)->default('other'); // id_card, electronics, documents, keys, bags, accessories, other
            $table->text('description');
            $table->string('location');
            $table->date('date_occurred')->nullable();
            $table->string('contact_info');
            $table->string('image')->nullable();
            $table->string('status', 20)->default('open')->index(); // open, claimed, resolved
            $table->string('claimed_by_info')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lost_found_items');
        Schema::dropIfExists('help_ticket_replies');
        Schema::dropIfExists('help_tickets');
        Schema::dropIfExists('accommodation_requests');
        Schema::dropIfExists('accommodations');
        Schema::dropIfExists('venue_bookings');
        Schema::dropIfExists('venues');
    }
};
