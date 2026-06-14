<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_endpoint_returns_standard_json_response(): void
    {
        $this->seed();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@siat.local',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user' => [
                        'name',
                        'email',
                        'roles',
                        'permissions',
                    ],
                ],
                'meta',
                'errors',
            ]);
    }

    public function test_forgot_and_reset_password_flow_returns_reset_token_and_updates_password(): void
    {
        $this->seed();

        $forgot = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'admin@siat.local',
        ]);

        $forgot->assertOk()
            ->assertJsonPath('data.email', 'admin@siat.local')
            ->assertJsonPath('data.token_created', true);

        $token = $forgot->json('data.reset_token');

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'admin@siat.local',
            'token' => $token,
            'password' => 'password456',
            'password_confirmation' => 'password456',
        ])->assertOk()
            ->assertJsonPath('data.password_reset', true);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@siat.local',
            'password' => 'password456',
            'role' => 'admin',
        ])->assertOk();
    }

    public function test_authenticated_user_can_change_password(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'admin@siat.local')->firstOrFail();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/auth/change-password', [
            'current_password' => 'password123',
            'new_password' => 'password789',
            'new_password_confirmation' => 'password789',
        ])->assertOk()
            ->assertJsonPath('data.changed', true);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@siat.local',
            'password' => 'password789',
            'role' => 'admin',
        ])->assertOk();
    }
}
