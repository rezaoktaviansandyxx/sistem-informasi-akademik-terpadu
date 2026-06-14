<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class KrsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_krs_endpoint_returns_student_draft(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'student@siat.local')->firstOrFail();
        $student = Student::query()->where('user_id', $user->id)->firstOrFail();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/krs/current?student_id='.$student->id)
            ->assertOk()
            ->assertJsonPath('data.student_id', $student->id);
    }
}
