<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MasterDataApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_master_students(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@siat.local')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/master/students')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'items' => [
                        '*' => [
                            'id',
                            'nim',
                            'name',
                            'academic_status',
                            'user',
                            'study_program',
                        ],
                    ],
                    'pagination' => [
                        'current_page',
                        'per_page',
                        'total',
                        'last_page',
                    ],
                ],
            ]);
    }

    public function test_student_cannot_access_admin_master_data_endpoint(): void
    {
        $this->seed();

        $student = User::query()->where('email', 'student@siat.local')->firstOrFail();
        Sanctum::actingAs($student);

        $this->getJson('/api/v1/master/students')
            ->assertForbidden()
            ->assertJsonPath('message', 'Akses ditolak untuk role ini.');
    }

    public function test_admin_can_create_course_via_master_data_endpoint(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@siat.local')->firstOrFail();
        $studyProgram = StudyProgram::query()->where('code', 'TI')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/master/courses', [
            'study_program_id' => $studyProgram->id,
            'code' => 'IF310',
            'name' => 'Arsitektur Sistem Informasi',
            'credits' => 3,
        ])
            ->assertCreated()
            ->assertJsonPath('data.code', 'IF310')
            ->assertJsonPath('data.study_program.code', 'TI');

        $this->assertDatabaseHas((new Course())->getTable(), [
            'code' => 'IF310',
            'name' => 'Arsitektur Sistem Informasi',
        ]);
    }

    public function test_admin_can_list_faculties_with_standard_payload(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@siat.local')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/master/faculties')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'items' => [
                        '*' => [
                            'id',
                            'name',
                            'study_programs_count',
                        ],
                    ],
                    'pagination',
                ],
            ]);
    }

    public function test_admin_can_filter_study_programs_by_faculty(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@siat.local')->firstOrFail();
        $faculty = Faculty::query()->where('name', 'Fakultas Teknik')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/master/study-programs?faculty_id='.$faculty->id)
            ->assertOk()
            ->assertJsonPath('data.items.0.faculty.id', $faculty->id)
            ->assertJsonPath('data.items.0.code', 'TI');
    }

    public function test_admin_can_create_semester_via_master_reference_endpoint(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@siat.local')->firstOrFail();
        $academicYear = AcademicYear::query()->where('label', '2025/2026')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/master/semesters', [
            'academic_year_id' => $academicYear->id,
            'name' => 'Pendek',
            'is_active' => false,
        ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Pendek')
            ->assertJsonPath('data.academic_year.id', $academicYear->id)
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas((new Semester())->getTable(), [
            'academic_year_id' => $academicYear->id,
            'name' => 'Pendek',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_filter_and_sort_students(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@siat.local')->firstOrFail();
        $studyProgram = StudyProgram::query()->where('code', 'TI')->firstOrFail();

        $activeUser = User::query()->create([
            'name' => 'Mahasiswa Aktif Tambahan',
            'email' => 'student2@siat.local',
            'password' => 'password123',
        ]);

        Student::query()->create([
            'user_id' => $activeUser->id,
            'study_program_id' => $studyProgram->id,
            'nim' => '2410002',
            'name' => 'Mahasiswa Aktif Tambahan',
            'academic_status' => 'active',
        ]);

        $inactiveUser = User::query()->create([
            'name' => 'Mahasiswa Cuti',
            'email' => 'student3@siat.local',
            'password' => 'password123',
        ]);

        Student::query()->create([
            'user_id' => $inactiveUser->id,
            'study_program_id' => $studyProgram->id,
            'nim' => '2410003',
            'name' => 'Mahasiswa Cuti',
            'academic_status' => 'leave',
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/master/students?study_program_id='.$studyProgram->id.'&academic_status=active&sort_by=nim&sort_direction=desc')
            ->assertOk()
            ->assertJsonPath('data.items.0.nim', '2410002')
            ->assertJsonPath('data.items.1.nim', '2310001');
    }

    public function test_admin_can_filter_and_sort_courses(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@siat.local')->firstOrFail();
        $studyProgram = StudyProgram::query()->where('code', 'TI')->firstOrFail();

        Course::query()->create([
            'study_program_id' => $studyProgram->id,
            'code' => 'IF400',
            'name' => 'Analitik Akademik',
            'credits' => 4,
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/master/courses?study_program_id='.$studyProgram->id.'&sort_by=credits&sort_direction=desc')
            ->assertOk()
            ->assertJsonPath('data.items.0.code', 'IF400')
            ->assertJsonPath('data.items.0.credits', 4);
    }

    public function test_admin_can_filter_empty_faculties_and_sort_them(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@siat.local')->firstOrFail();

        Faculty::query()->create([
            'name' => 'Fakultas Vokasi',
        ]);

        Faculty::query()->create([
            'name' => 'Fakultas Bahasa',
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/master/faculties?has_study_programs=false&sort_by=name&sort_direction=desc')
            ->assertOk()
            ->assertJsonPath('data.items.0.name', 'Fakultas Vokasi')
            ->assertJsonPath('data.items.0.study_programs_count', 0);
    }

    public function test_admin_can_filter_inactive_academic_years_and_sort_by_label(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@siat.local')->firstOrFail();

        AcademicYear::query()->create([
            'label' => '2024/2025',
            'is_active' => false,
        ]);

        AcademicYear::query()->create([
            'label' => '2023/2024',
            'is_active' => false,
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/master/academic-years?is_active=false&sort_by=label&sort_direction=asc')
            ->assertOk()
            ->assertJsonPath('data.items.0.label', '2023/2024')
            ->assertJsonPath('data.items.1.label', '2024/2025');
    }

    public function test_admin_can_filter_semesters_by_academic_year_and_sort_by_name(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@siat.local')->firstOrFail();
        $academicYear = AcademicYear::query()->where('label', '2025/2026')->firstOrFail();

        Semester::query()->create([
            'academic_year_id' => $academicYear->id,
            'name' => 'Ganjil',
            'is_active' => false,
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/master/semesters?academic_year_id='.$academicYear->id.'&sort_by=name&sort_direction=asc')
            ->assertOk()
            ->assertJsonPath('data.items.0.name', 'Ganjil')
            ->assertJsonPath('data.items.1.name', 'Genap');
    }
}
