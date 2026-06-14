<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(array $credentials): array
    {
        $user = User::query()
            ->with('roles.permissions')
            ->where('email', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial tidak valid.'],
            ]);
        }

        if (! $user->roles->pluck('code')->contains($credentials['role'])) {
            throw ValidationException::withMessages([
                'role' => ['Role yang dipilih tidak dimiliki oleh pengguna ini.'],
            ]);
        }

        $token = method_exists($user, 'createToken')
            ? $user->createToken('siat-web')->plainTextToken
            : 'sanctum-demo-token';

        return [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'active_role' => $credentials['role'],
                'roles' => $user->roles->pluck('code')->values()->all(),
                'permissions' => $user->roles
                    ->flatMap(fn ($role) => $role->permissions->pluck('code'))
                    ->unique()
                    ->values()
                    ->all(),
            ],
        ];
    }

    public function logout(Request $request): void
    {
        $request->user()?->currentAccessToken()?->delete();
    }

    public function profile(?User $user): array
    {
        $user?->loadMissing('roles.permissions');

        if (! $user) {
            return [
                'id' => null,
                'name' => 'Guest',
                'email' => 'guest@siat.local',
                'active_role' => null,
                'roles' => [],
                'permissions' => [],
            ];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'active_role' => $user->roles->first()?->code,
            'roles' => $user->roles->pluck('code')->values()->all(),
            'permissions' => $user->roles
                ->flatMap(fn ($role) => $role->permissions->pluck('code'))
                ->unique()
                ->values()
                ->all(),
        ];
    }

    public function forgotPassword(string $email): array
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return [
                'email' => $email,
                'token_created' => false,
            ];
        }

        $plainToken = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($plainToken),
                'created_at' => now(),
            ]
        );

        return [
            'email' => $email,
            'token_created' => true,
            'delivery' => app()->environment(['local', 'testing']) ? 'preview' : 'email',
            'reset_token' => app()->environment(['local', 'testing']) ? $plainToken : null,
            'reset_token_preview' => app()->environment(['local', 'testing']) ? $plainToken : null,
        ];
    }

    public function resetPassword(array $payload): array
    {
        $row = DB::table('password_reset_tokens')
            ->where('email', $payload['email'])
            ->first();

        if (! $row || ! Hash::check($payload['token'], $row->token)) {
            throw ValidationException::withMessages([
                'token' => ['Token reset password tidak valid.'],
            ]);
        }

        $user = User::query()->where('email', $payload['email'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['Pengguna tidak ditemukan.'],
            ]);
        }

        $user->update([
            'password' => $payload['password'],
        ]);

        DB::table('password_reset_tokens')
            ->where('email', $payload['email'])
            ->delete();

        return [
            'email' => $user->email,
            'password_reset' => true,
        ];
    }

    public function changePassword(User $user, array $payload): array
    {
        if (! Hash::check($payload['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password lama tidak sesuai.'],
            ]);
        }

        $user->update([
            'password' => $payload['new_password'],
        ]);

        return [
            'changed' => true,
            'user_id' => $user->id,
        ];
    }

    public function sessions(User $user): array
    {
        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(fn ($session): array => [
                'id' => $session->id,
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'last_activity_at' => now()->setTimestamp((int) $session->last_activity)->toIso8601String(),
            ])
            ->values()
            ->all();

        return [
            'items' => $sessions,
            'total' => count($sessions),
        ];
    }

    public function logoutOtherSessions(User $user, string $currentSessionId): array
    {
        $deleted = DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '!=', $currentSessionId)
            ->delete();

        return [
            'deleted_sessions' => $deleted,
        ];
    }
}
