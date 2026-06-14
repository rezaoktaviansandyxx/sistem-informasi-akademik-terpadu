<?php

namespace App\Services\Dashboard;

use App\Models\AcademicClass;
use App\Models\Approval;
use App\Models\Course;
use App\Models\GradeRecord;
use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function summary(string $role): array
    {
        return [
            'role' => $role,
            'cards' => $this->cards($role),
            'charts' => [
                [
                    'key' => 'krs_progress',
                    'label' => 'Progress KRS',
                    'points' => [65, 73, 81, 91],
                ],
            ],
            'todos' => [
                [
                    'id' => 'todo-1',
                    'title' => 'Finalisasi proses penting hari ini',
                    'status' => 'pending',
                    'due_at' => now()->addHours(6)->toIso8601String(),
                ],
            ],
        ];
    }

    private function cards(string $role): array
    {
        return match ($role) {
            'admin' => [
                ['key' => 'approvals', 'label' => 'Approval Pending', 'value' => Approval::query()->where('status', 'pending')->count(), 'trend' => 'padat'],
                ['key' => 'students', 'label' => 'Mahasiswa Aktif', 'value' => Student::query()->count(), 'trend' => 'baik'],
                ['key' => 'letters', 'label' => 'Surat Pending', 'value' => DB::table('academic_letters')->where('status', 'requested')->count(), 'trend' => 'perlu aksi'],
                ['key' => 'verifications', 'label' => 'Verifikasi Pending', 'value' => DB::table('verification_records')->where('status', 'pending')->count(), 'trend' => 'review'],
            ],
            'lecturer' => [
                ['key' => 'classes', 'label' => 'Kelas Aktif', 'value' => AcademicClass::query()->count(), 'trend' => 'aktif'],
                ['key' => 'draft', 'label' => 'Nilai Draft', 'value' => GradeRecord::query()->where('status', 'draft')->count(), 'trend' => 'perlu aksi'],
                ['key' => 'teachings', 'label' => 'Pertemuan Mengajar', 'value' => DB::table('teaching_attendances')->count(), 'trend' => 'tercatat'],
            ],
            'leader' => [
                ['key' => 'students', 'label' => 'Mahasiswa', 'value' => Student::query()->count(), 'trend' => 'tercatat'],
                ['key' => 'lecturers', 'label' => 'Dosen', 'value' => Lecturer::query()->count(), 'trend' => 'tercatat'],
                ['key' => 'announcements', 'label' => 'Pengumuman Aktif', 'value' => DB::table('announcements')->where('status', 'published')->count(), 'trend' => 'aktif'],
                ['key' => 'letters', 'label' => 'Layanan Surat', 'value' => DB::table('academic_letters')->count(), 'trend' => 'monitor'],
            ],
            'super_admin' => [
                ['key' => 'users', 'label' => 'Pengguna', 'value' => DB::table('users')->count(), 'trend' => 'terkelola'],
                ['key' => 'roles', 'label' => 'Role', 'value' => DB::table('roles')->count(), 'trend' => 'rbac'],
                ['key' => 'settings', 'label' => 'Setting Sistem', 'value' => DB::table('system_settings')->count(), 'trend' => 'aman'],
                ['key' => 'audit', 'label' => 'Audit Event', 'value' => DB::table('audit_logs')->count(), 'trend' => 'tercatat'],
            ],
            default => [
                ['key' => 'courses', 'label' => 'Mata Kuliah Tersedia', 'value' => Course::query()->count(), 'trend' => 'aman'],
                ['key' => 'classes', 'label' => 'Kelas Dibuka', 'value' => AcademicClass::query()->count(), 'trend' => 'stabil'],
                ['key' => 'attendance', 'label' => 'Presensi Masuk', 'value' => DB::table('student_attendances')->where('status', 'present')->count(), 'trend' => 'baik'],
                ['key' => 'status', 'label' => 'Status Akademik', 'value' => 'aktif', 'trend' => 'monitor'],
            ],
        };
    }
}
