<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timer_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('category', 30)->default('charity'); // charity, election, event, deadline, urgent
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at');
            $table->string('cta_label', 60)->nullable();
            $table->string('cta_url')->nullable();
            $table->unsignedBigInteger('target_amount')->nullable();
            $table->unsignedBigInteger('raised_amount')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('category', 30)->default('general'); // general, academic, welfare, election, sports, charity, urgent
            $table->boolean('is_urgent')->default(false)->index();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_published')->default(true)->index();
            $table->string('cover_image')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('announcement_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('announcement_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('read_at');
            $table->unique(['announcement_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_reads');
        Schema::dropIfExists('announcement_comments');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('timer_announcements');
    }
};
