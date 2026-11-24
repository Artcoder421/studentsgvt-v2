<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('reg_no', 50)->nullable()->unique()->after('email');
            $table->string('role', 30)->default('student')->index()->after('password'); // super_admin, admin, class_rep, student
            $table->foreignId('course_id')->nullable()->after('role')->constrained('courses')->nullOnDelete();
            $table->unsignedSmallInteger('year_joined')->nullable()->after('course_id');
            $table->string('academic_level', 40)->nullable()->after('year_joined');
            $table->string('phone', 30)->nullable()->after('academic_level');
            $table->string('avatar')->nullable()->after('phone');
            $table->text('bio')->nullable()->after('avatar');
            $table->boolean('is_active')->default(true)->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('course_id');
            $table->dropColumn([
                'reg_no',
                'role',
                'year_joined',
                'academic_level',
                'phone',
                'avatar',
                'bio',
                'is_active',
            ]);
        });
    }
};
