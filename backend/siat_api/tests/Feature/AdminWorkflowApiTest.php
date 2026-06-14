<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminWorkflowApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_roles_and_upsert_setting(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@siat.local')->firstOrFail();
        $student = User::query()->where('email', 'student@siat.local')->firstOrFail();
        Sanctum::actingAs($admin);

        $this->putJson('/api/v1/security/users/'.$student->id.'/roles', [
            'role_codes' => ['student', 'leader'],
        ])->assertOk()
            ->assertJsonPath('data.roles.0', 'student');

        $this->postJson('/api/v1/security/settings', [
            'key' => 'portal.theme',
            'label' => 'Tema Portal',
            'value' => 'institutional-blue',
        ])->assertOk()
            ->assertJsonPath('data.key', 'portal.theme');
    }

    public function test_admin_cannot_create_conflicting_schedule(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@siat.local')->firstOrFail();
        $class = AcademicClass::query()->firstOrFail();
        $lecturerId = DB::table('class_schedules')->value('lecturer_id');
        $roomId = DB::table('class_schedules')->value('room_id');
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/master/schedules', [
            'academic_class_id' => $class->id,
            'lecturer_id' => $lecturerId,
            'room_id' => $roomId,
            'day_of_week' => 1,
            'start_time' => '10:00',
            'end_time' => '11:00',
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Konflik jadwal terdeteksi.');
    }

    public function test_admin_can_decide_approval(): void
    {
        $this->seed();

        $admin = User::query()->where('email', 'admin@siat.local')->firstOrFail();
        $approvalId = DB::table('approvals')->value('id');
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/approvals/'.$approvalId.'/decision', [
            'decision' => 'approved',
            'notes' => 'Disetujui oleh admin.',
        ])->assertOk()
            ->assertJsonPath('data.status', 'approved');
    }
}
