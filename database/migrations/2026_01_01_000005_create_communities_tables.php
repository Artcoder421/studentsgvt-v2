<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('type', 30)->default('course_group')->index(); // course_default, course_group, general
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignId('cr_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_official')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('community_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained('communities')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('member_role', 20)->default('member'); // member, moderator, cr, admin
            $table->timestamp('joined_at');
            $table->unique(['community_id', 'user_id']);
        });

        Schema::create('community_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained('communities')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reply_to_id')->nullable()->constrained('community_messages')->nullOnDelete();
            $table->text('body');
            $table->string('attachment')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_messages');
        Schema::dropIfExists('community_members');
        Schema::dropIfExists('communities');
    }
};
