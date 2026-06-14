<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faculties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('study_programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('faculty_id');
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('study_program_id');
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedSmallInteger('credits');
            $table->timestamps();
        });

        Schema::create('academic_years', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('label');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('semesters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('academic_year_id');
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('academic_classes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('course_id');
            $table->uuid('semester_id');
            $table->string('name');
            $table->unsignedSmallInteger('capacity');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_classes');
        Schema::dropIfExists('semesters');
        Schema::dropIfExists('academic_years');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('study_programs');
        Schema::dropIfExists('faculties');
    }
};
