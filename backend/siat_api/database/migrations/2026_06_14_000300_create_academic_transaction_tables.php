<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('study_program_id');
            $table->string('nim')->unique();
            $table->string('name');
            $table->string('academic_status')->default('active');
            $table->timestamps();
        });

        Schema::create('lecturers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nidn')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('krs_headers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->uuid('semester_id');
            $table->string('status')->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('krs_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('krs_header_id');
            $table->uuid('academic_class_id');
            $table->timestamps();
        });

        Schema::create('grade_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('academic_class_id');
            $table->uuid('student_id');
            $table->decimal('assignment_score', 5, 2)->nullable();
            $table->decimal('mid_score', 5, 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable();
            $table->decimal('final_numeric', 5, 2)->nullable();
            $table->string('final_letter', 2)->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_records');
        Schema::dropIfExists('krs_details');
        Schema::dropIfExists('krs_headers');
        Schema::dropIfExists('lecturers');
        Schema::dropIfExists('students');
    }
};
