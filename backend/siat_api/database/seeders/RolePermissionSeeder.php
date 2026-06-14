<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['code' => 'super_admin', 'name' => 'Super Admin'],
            ['code' => 'admin', 'name' => 'Admin Akademik'],
            ['code' => 'lecturer', 'name' => 'Dosen'],
            ['code' => 'student', 'name' => 'Mahasiswa'],
            ['code' => 'leader', 'name' => 'Pimpinan'],
        ];

        $permissions = [
            ['code' => 'dashboard.view', 'name' => 'Lihat Dashboard'],
            ['code' => 'students.manage', 'name' => 'Kelola Mahasiswa'],
            ['code' => 'grades.manage', 'name' => 'Kelola Nilai'],
            ['code' => 'approvals.manage', 'name' => 'Kelola Approval'],
            ['code' => 'reports.view', 'name' => 'Lihat Laporan'],
            ['code' => 'krs.manage', 'name' => 'Kelola KRS'],
            ['code' => 'announcements.manage', 'name' => 'Kelola Pengumuman'],
            ['code' => 'calendar.manage', 'name' => 'Kelola Kalender Akademik'],
            ['code' => 'letters.manage', 'name' => 'Kelola Surat Akademik'],
            ['code' => 'verifications.manage', 'name' => 'Kelola Verifikasi'],
            ['code' => 'security.manage', 'name' => 'Kelola Keamanan dan RBAC'],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['code' => $role['code']],
                ['id' => (string) Str::uuid(), 'name' => $role['name']]
            );
        }

        foreach ($permissions as $permission) {
            Permission::query()->updateOrCreate(
                ['code' => $permission['code']],
                ['id' => (string) Str::uuid(), 'name' => $permission['name']]
            );
        }

        $permissionMap = Permission::query()->get()->keyBy('code');
        $roleMap = Role::query()->get()->keyBy('code');

        $roleMap['super_admin']?->permissions()->sync($permissionMap->pluck('id')->filter()->all());

        $roleMap['admin']?->permissions()->sync([
            $permissionMap['dashboard.view']?->id,
            $permissionMap['students.manage']?->id,
            $permissionMap['approvals.manage']?->id,
            $permissionMap['reports.view']?->id,
            $permissionMap['announcements.manage']?->id,
            $permissionMap['calendar.manage']?->id,
            $permissionMap['letters.manage']?->id,
            $permissionMap['verifications.manage']?->id,
        ]);

        $roleMap['lecturer']?->permissions()->sync([
            $permissionMap['dashboard.view']?->id,
            $permissionMap['grades.manage']?->id,
        ]);

        $roleMap['student']?->permissions()->sync([
            $permissionMap['dashboard.view']?->id,
            $permissionMap['krs.manage']?->id,
        ]);

        $roleMap['leader']?->permissions()->sync([
            $permissionMap['dashboard.view']?->id,
            $permissionMap['reports.view']?->id,
        ]);

        $admin = User::query()->where('email', 'admin@siat.local')->first();
        if ($admin && $roleMap['admin']) {
            $admin->roles()->syncWithoutDetaching([$roleMap['admin']->id]);
        }

        if ($admin && $roleMap['super_admin']) {
            $admin->roles()->syncWithoutDetaching([$roleMap['super_admin']->id]);
        }
    }
}
