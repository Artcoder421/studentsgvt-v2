<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 80)->unique();
            $table->text('value')->nullable();
            $table->string('group', 40)->default('general');
            $table->string('label')->nullable();
            $table->timestamps();
        });

        Schema::create('leaders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position');
            $table->string('category', 30)->default('executive'); // institute, executive, ministry, parliament
            $table->string('department')->nullable();
            $table->text('bio')->nullable();
            $table->text('quote')->nullable();
            $table->string('photo')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('office_location')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaders');
        Schema::dropIfExists('site_settings');
    }
};
