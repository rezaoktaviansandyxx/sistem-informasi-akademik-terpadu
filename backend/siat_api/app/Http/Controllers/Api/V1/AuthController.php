<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    public function login(LoginRequest $request)
    {
        return ApiResponse::success(
            $this->authService->login($request->validated()),
            'Login berhasil'
        );
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request);

        return ApiResponse::success(null, 'Logout berhasil');
    }

    public function me(Request $request)
    {
        return ApiResponse::success(
            $this->authService->profile($this->resolveUser($request)),
            'Profil pengguna aktif berhasil diambil'
        );
    }

    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        return ApiResponse::success(
            $this->authService->forgotPassword($validated['email']),
            'Permintaan reset password berhasil diproses'
        );
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string', 'min:16'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        return ApiResponse::success(
            $this->authService->resetPassword($validated),
            'Password berhasil direset'
        );
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string', 'min:8'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        return ApiResponse::success(
            $this->authService->changePassword($this->resolveUser($request), $validated),
            'Password berhasil diubah'
        );
    }

    public function sessions(Request $request)
    {
        return ApiResponse::success(
            $this->authService->sessions($this->resolveUser($request)),
            'Daftar sesi aktif berhasil diambil'
        );
    }

    public function logoutOtherSessions(Request $request)
    {
        $validated = $request->validate([
            'current_session_id' => ['required', 'string'],
        ]);

        return ApiResponse::success(
            $this->authService->logoutOtherSessions($this->resolveUser($request), $validated['current_session_id']),
            'Sesi lain berhasil dikeluarkan'
        );
    }

    private function resolveUser(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401, 'Pengguna belum login.');
        }

        return $user;
    }
}
