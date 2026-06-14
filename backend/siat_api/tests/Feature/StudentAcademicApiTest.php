<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentAcademicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_khs_and_transcript(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'student@siat.local')->firstOrFail();
        $student = Student::query()->where('user_id', $user->id)->firstOrFail();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/student/khs?student_id='.$student->id)
            ->assertOk()
            ->assertJsonPath('data.student.nim', '2310001')
            ->assertJsonPath('data.items.0.course_code', 'IF205');

        $this->getJson('/api/v1/student/transcript?student_id='.$student->id)
            ->assertOk()
            ->assertJsonPath('data.student.nim', '2310001')
            ->assertJsonPath('data.summary.total_credits', 3);
    }

    public function test_student_can_view_schedule_and_attendance(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'student@siat.local')->firstOrFail();
        $student = Student::query()->where('user_id', $user->id)->firstOrFail();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/student/schedule?student_id='.$student->id)
            ->assertOk()
            ->assertJsonPath('data.items.0.course_code', 'IF205')
            ->assertJsonPath('data.items.0.day', 'Senin');

        $this->getJson('/api/v1/student/attendance?student_id='.$student->id)
            ->assertOk()
            ->assertJsonPath('data.items.0.course_code', 'IF205')
            ->assertJsonPath('data.items.0.summary.present', 1);
    }
}
