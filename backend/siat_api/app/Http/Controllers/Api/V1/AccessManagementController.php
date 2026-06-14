<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccessManagementController extends Controller
{
    public function users()
    {
        $items = User::query()
            ->with('roles.permissions')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('code')->values()->all(),
                'permissions' => $user->roles
                    ->flatMap(fn ($role) => $role->permissions->pluck('code'))
                    ->unique()
                    ->values()
                    ->all(),
            ])
            ->values();

        return ApiResponse::success(['items' => $items], 'Daftar pengguna berhasil diambil');
    }

    public function assignRoles(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_codes' => ['required', 'array', 'min:1'],
            'role_codes.*' => ['string', 'exists:roles,code'],
        ]);

        $roleIds = Role::query()
            ->whereIn('code', $validated['role_codes'])
            ->pluck('id')
            ->all();

        $user->roles()->sync($roleIds);
        $user->load('roles.permissions');

        return ApiResponse::success([
            'id' => $user->id,
            'roles' => $user->roles->pluck('code')->values()->all(),
        ], 'Role pengguna berhasil diperbarui');
    }

    public function roles()
    {
        $items = Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role): array => [
                'id' => $role->id,
                'code' => $role->code,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('code')->values()->all(),
            ])
            ->values();

        return ApiResponse::success(['items' => $items], 'Daftar role berhasil diambil');
    }

    public function permissions()
    {
        $items = Permission::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return ApiResponse::success(['items' => $items], 'Daftar permission berhasil diambil');
    }

    public function settings()
    {
        $items = DB::table('system_settings')
            ->orderBy('label')
            ->get();

        return ApiResponse::success(['items' => $items], 'Konfigurasi sistem berhasil diambil');
    }

    public function upsertSetting(Request $request)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100'],
            'label' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'string'],
        ]);

        $existing = DB::table('system_settings')->where('key', $validated['key'])->first();

        if ($existing) {
            DB::table('system_settings')
                ->where('key', $validated['key'])
                ->update([
                    'label' => $validated['label'],
                    'value' => $validated['value'],
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('system_settings')->insert([
                'id' => (string) Str::uuid(),
                'key' => $validated['key'],
                'label' => $validated['label'],
                'value' => $validated['value'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return ApiResponse::success($validated, 'Konfigurasi sistem berhasil disimpan');
    }
}
