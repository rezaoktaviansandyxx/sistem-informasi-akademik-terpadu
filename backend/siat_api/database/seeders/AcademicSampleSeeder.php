<?php

namespace Database\Seeders;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Approval;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\GradeRecord;
use App\Models\KrsDetail;
use App\Models\KrsHeader;
use App\Models\Lecturer;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AcademicSampleSeeder extends Seeder
{
    public function run(): void
    {
        $faculty = Faculty::query()->firstOrCreate(
            ['name' => 'Fakultas Teknik'],
        );

        $program = StudyProgram::query()->firstOrCreate(
            ['code' => 'TI'],
            [
                'faculty_id' => $faculty->id,
                'name' => 'Teknik Informatika',
            ]
        );

        $year = AcademicYear::query()->firstOrCreate(
            ['label' => '2025/2026'],
            ['is_active' => true]
        );

        $semester = Semester::query()->firstOrCreate(
            ['name' => 'Genap'],
            [
                'academic_year_id' => $year->id,
                'is_active' => true,
            ]
        );

        $course = Course::query()->firstOrCreate(
            ['code' => 'IF205'],
            [
                'study_program_id' => $program->id,
                'name' => 'Basis Data Lanjut',
                'credits' => 3,
            ]
        );

        $class = AcademicClass::query()->firstOrCreate(
            [
                'course_id' => $course->id,
                'semester_id' => $semester->id,
                'name' => 'A',
            ],
            ['capacity' => 35]
        );

        $studentUser = User::query()->updateOrCreate(
            ['email' => 'student@siat.local'],
            ['name' => 'Mahasiswa Demo', 'password' => 'password123']
        );

        $lecturerUser = User::query()->updateOrCreate(
            ['email' => 'lecturer@siat.local'],
            ['name' => 'Dosen Demo', 'password' => 'password123']
        );

        $studentRole = Role::query()->where('code', 'student')->first();
        $lecturerRole = Role::query()->where('code', 'lecturer')->first();

        if ($studentRole) {
            $studentUser->roles()->syncWithoutDetaching([$studentRole->id]);
        }

        if ($lecturerRole) {
            $lecturerUser->roles()->syncWithoutDetaching([$lecturerRole->id]);
        }

        $student = Student::query()->firstOrCreate(
            ['nim' => '2310001'],
            [
                'user_id' => $studentUser->id,
                'study_program_id' => $program->id,
                'name' => 'Mahasiswa Demo',
                'academic_status' => 'active',
            ]
        );

        Lecturer::query()->firstOrCreate(
            ['nidn' => '0123456789'],
            [
                'user_id' => $lecturerUser->id,
                'name' => 'Dosen Demo',
            ]
        );

        $lecturer = Lecturer::query()->where('nidn', '0123456789')->firstOrFail();

        $krsHeader = KrsHeader::query()->firstOrCreate(
            [
                'student_id' => $student->id,
                'semester_id' => $semester->id,
            ],
            ['status' => 'draft']
        );

        KrsDetail::query()->firstOrCreate(
            [
                'krs_header_id' => $krsHeader->id,
                'academic_class_id' => $class->id,
            ]
        );

        GradeRecord::query()->updateOrCreate(
            [
                'academic_class_id' => $class->id,
                'student_id' => $student->id,
            ],
            [
                'assignment_score' => 84,
                'mid_score' => 82,
                'final_score' => 88,
                'final_numeric' => 85.00,
                'final_letter' => 'A',
                'status' => 'draft',
            ]
        );

        Approval::query()->firstOrCreate(
            ['title' => 'Koreksi Nilai Basis Data Lanjut'],
            [
                'type' => 'grade_revision',
                'status' => 'pending',
                'notes' => 'Menunggu review admin akademik.',
            ]
        );

        DB::table('departments')->updateOrInsert(
            ['code' => 'IF'],
            [
                'id' => (string) Str::uuid(),
                'faculty_id' => $faculty->id,
                'name' => 'Jurusan Informatika',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('curricula')->updateOrInsert(
            ['code' => 'TI-2025'],
            [
                'id' => (string) Str::uuid(),
                'study_program_id' => $program->id,
                'name' => 'Kurikulum 2025 Teknik Informatika',
                'total_credits' => 144,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('rooms')->updateOrInsert(
            ['code' => 'LAB-301'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Laboratorium Basis Data',
                'building' => 'Gedung A',
                'capacity' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $roomId = DB::table('rooms')->where('code', 'LAB-301')->value('id');

        DB::table('class_schedules')->updateOrInsert(
            ['academic_class_id' => $class->id],
            [
                'id' => (string) Str::uuid(),
                'lecturer_id' => $lecturer->id,
                'room_id' => $roomId,
                'day_of_week' => 1,
                'start_time' => '09:40',
                'end_time' => '12:10',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('teaching_attendances')->updateOrInsert(
            [
                'academic_class_id' => $class->id,
                'lecturer_id' => $lecturer->id,
                'meeting_no' => 1,
            ],
            [
                'id' => (string) Str::uuid(),
                'topic' => 'Kontrak kuliah dan pengantar basis data lanjut',
                'held_on' => now()->subDays(7)->toDateString(),
                'status' => 'held',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('student_attendances')->updateOrInsert(
            [
                'academic_class_id' => $class->id,
                'student_id' => $student->id,
                'meeting_no' => 1,
            ],
            [
                'id' => (string) Str::uuid(),
                'status' => 'present',
                'notes' => 'Hadir tepat waktu.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('announcements')->updateOrInsert(
            ['title' => 'Pembukaan Periode KRS'],
            [
                'id' => (string) Str::uuid(),
                'content' => 'Periode KRS semester genap resmi dibuka.',
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('academic_calendar_events')->updateOrInsert(
            ['title' => 'Periode KRS'],
            [
                'id' => (string) Str::uuid(),
                'category' => 'krs',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(7)->toDateString(),
                'status' => 'published',
                'notes' => 'Mahasiswa melakukan penyusunan KRS online.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('academic_letters')->updateOrInsert(
            [
                'student_id' => $student->id,
                'title' => 'Surat Keterangan Aktif Kuliah',
            ],
            [
                'id' => (string) Str::uuid(),
                'type' => 'active-study',
                'status' => 'requested',
                'requested_at' => now()->subDay(),
                'processed_at' => null,
                'notes' => 'Menunggu verifikasi admin akademik.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('verification_records')->updateOrInsert(
            [
                'subject_type' => 'student_profile',
                'subject_id' => $student->id,
            ],
            [
                'id' => (string) Str::uuid(),
                'type' => 'profile_change',
                'status' => 'pending',
                'old_payload' => json_encode(['phone' => '081111111111']),
                'new_payload' => json_encode(['phone' => '082222222222']),
                'evidence_url' => 'https://example.test/bukti-perubahan.pdf',
                'notes' => 'Perubahan nomor telepon menunggu verifikasi.',
                'verified_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('system_settings')->updateOrInsert(
            ['key' => 'krs.max_credits'],
            [
                'id' => (string) Str::uuid(),
                'label' => 'Batas Maksimal SKS KRS',
                'value' => '24',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
