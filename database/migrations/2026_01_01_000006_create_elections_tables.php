<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('academic_year', 20);
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('applications_open')->default(true);
            $table->boolean('voting_open')->default(true);
            $table->boolean('results_published')->default(false);
            $table->timestamp('voting_starts_at')->nullable();
            $table->timestamp('voting_ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained('elections')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('fullname');
            $table->string('reg_no', 50);
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->string('academic_level', 40);
            $table->string('position', 60)->index(); // President, Vice President, Parliament Representative, General Secretary, Treasurer
            $table->string('motto');
            $table->text('manifesto');
            $table->string('photo')->nullable();
            $table->string('status', 20)->default('pending')->index(); // pending, approved, declined
            $table->timestamps();
        });

        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_id')->constrained('elections')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->foreignId('voter_id')->constrained('users')->cascadeOnDelete();
            $table->string('position', 60);
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->timestamp('voted_at');
            $table->unique(['election_id', 'position', 'voter_id'], 'votes_unique_per_position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
        Schema::dropIfExists('candidates');
        Schema::dropIfExists('elections');
    }
};
