<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_endpoint_returns_summary_payload(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'admin@siat.local')->firstOrFail();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/dashboard?role=admin')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'role',
                    'cards',
                    'charts',
                    'todos',
                ],
            ]);
    }
}
