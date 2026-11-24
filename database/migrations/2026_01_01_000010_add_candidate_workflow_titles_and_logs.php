<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Official elected SONIT title on users (auto-assigned after election)
        Schema::table('users', function (Blueprint $table) {
            $table->string('sonit_title', 120)->nullable()->after('role');
        });

        // 2. Link leaders to user_id & election_id for automatic post-election promotion
        Schema::table('leaders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->foreignId('election_id')->nullable()->after('user_id')->constrained('elections')->nullOnDelete();
        });

        // 3. Full candidate contact, application fee payment, and interview screening workflow
        Schema::table('candidates', function (Blueprint $table) {
            $table->string('contact_email', 120)->nullable()->after('reg_no');
            $table->string('contact_phone', 40)->nullable()->after('contact_email');
            $table->unsignedBigInteger('application_fee_amount')->default(10000)->after('photo');
            $table->string('payment_reference', 80)->nullable()->after('application_fee_amount');
            $table->string('payment_status', 25)->default('unpaid')->after('payment_reference'); // unpaid, submitted, verified
            $table->string('interview_status', 25)->default('pending_payment')->after('payment_status'); // pending_payment, scheduled, passed, failed
            $table->string('interview_date', 100)->nullable()->after('interview_status');
            $table->text('interview_notes')->nullable()->after('interview_date');
        });

        // 4. System-wide Activity Audit Logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name', 120)->nullable();
            $table->string('user_role', 40)->nullable();
            $table->string('module', 50)->index();
            $table->string('action', 80)->index();
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        // 5. Visitor Traffic Logs (Guests & Logged-In Users)
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->index();
            $table->string('method', 10)->default('GET');
            $table->string('path', 255)->index();
            $table->string('referer', 255)->nullable();
            $table->string('device_type', 20)->default('desktop');
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('visited_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
        Schema::dropIfExists('activity_logs');

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn([
                'contact_email',
                'contact_phone',
                'application_fee_amount',
                'payment_reference',
                'payment_status',
                'interview_status',
                'interview_date',
                'interview_notes',
            ]);
        });

        Schema::table('leaders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('election_id');
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('sonit_title');
        });
    }
};
