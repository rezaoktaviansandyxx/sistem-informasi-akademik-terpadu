<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GovernanceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_announcement_and_view_reports(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@siat.local')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/announcements', [
            'title' => 'Uji Pengumuman',
            'content' => 'Pengumuman untuk pengujian.',
            'status' => 'published',
        ])->assertCreated()
            ->assertJsonPath('data.title', 'Uji Pengumuman');

        $this->getJson('/api/v1/reports')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'summary' => [
                        'students',
                        'lecturers',
                        'approvals_pending',
                    ],
                    'exports',
                ],
            ]);
    }

    public function test_admin_can_create_letter_and_verification_record(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@siat.local')->firstOrFail();
        $student = Student::query()->where('nim', '2310001')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/academic-letters', [
            'student_id' => $student->id,
            'type' => 'active-study',
            'title' => 'Surat Aktif Kuliah Tambahan',
            'status' => 'requested',
            'notes' => 'Pengajuan baru',
        ])->assertCreated()
            ->assertJsonPath('data.student_id', $student->id);

        $this->postJson('/api/v1/verifications', [
            'type' => 'profile_change',
            'subject_type' => 'student_profile',
            'subject_id' => $student->id,
            'status' => 'pending',
            'old_payload' => ['address' => 'Alamat lama'],
            'new_payload' => ['address' => 'Alamat baru'],
        ])->assertCreated()
            ->assertJsonPath('data.subject_id', $student->id);
    }
}
