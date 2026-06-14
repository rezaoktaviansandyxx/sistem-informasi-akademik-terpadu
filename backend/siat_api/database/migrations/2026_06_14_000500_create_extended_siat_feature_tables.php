<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('faculty_id');
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('curricula', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('study_program_id');
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedSmallInteger('total_credits')->default(144);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('course_prerequisites', function (Blueprint $table) {
            $table->uuid('course_id');
            $table->uuid('prerequisite_course_id');
            $table->primary(['course_id', 'prerequisite_course_id']);
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('building')->nullable();
            $table->unsignedSmallInteger('capacity')->default(40);
            $table->timestamps();
        });

        Schema::create('class_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('academic_class_id');
            $table->uuid('lecturer_id')->nullable();
            $table->uuid('room_id')->nullable();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        Schema::create('teaching_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('academic_class_id');
            $table->uuid('lecturer_id');
            $table->unsignedTinyInteger('meeting_no');
            $table->string('topic');
            $table->date('held_on');
            $table->string('status')->default('held');
            $table->timestamps();
        });

        Schema::create('student_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('academic_class_id');
            $table->uuid('student_id');
            $table->unsignedTinyInteger('meeting_no');
            $table->string('status')->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('content');
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('academic_calendar_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('category');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('published');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('academic_letters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->string('type');
            $table->string('title');
            $table->string('status')->default('requested');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('verification_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('subject_type');
            $table->string('subject_id');
            $table->string('status')->default('pending');
            $table->json('old_payload')->nullable();
            $table->json('new_payload')->nullable();
            $table->string('evidence_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->string('label');
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('verification_records');
        Schema::dropIfExists('academic_letters');
        Schema::dropIfExists('academic_calendar_events');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('student_attendances');
        Schema::dropIfExists('teaching_attendances');
        Schema::dropIfExists('class_schedules');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('course_prerequisites');
        Schema::dropIfExists('curricula');
        Schema::dropIfExists('departments');
    }
};
