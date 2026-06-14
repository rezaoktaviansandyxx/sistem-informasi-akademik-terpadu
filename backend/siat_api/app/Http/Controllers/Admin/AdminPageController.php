<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Approval;
use App\Models\AuditLog;
use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\View\View;

class AdminPageController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'approvals_pending' => Approval::query()->where('status', 'pending')->count(),
                'students_total' => Student::query()->count(),
                'lecturers_total' => Lecturer::query()->count(),
                'audit_total' => AuditLog::query()->count(),
            ],
            'recentApprovals' => Approval::query()
                ->latest()
                ->limit(5)
                ->get(),
            'recentActivities' => ActivityLog::query()
                ->with('user')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    public function students(): View
    {
        return view('admin.students', [
            'students' => Student::query()
                ->with(['user', 'studyProgram.faculty'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function lecturers(): View
    {
        return view('admin.lecturers', [
            'lecturers' => Lecturer::query()
                ->with('user')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function approvals(): View
    {
        return view('admin.approvals', [
            'approvals' => Approval::query()
                ->latest()
                ->get(),
        ]);
    }

    public function auditLogs(): View
    {
        return view('admin.audit-logs', [
            'auditLogs' => AuditLog::query()
                ->with('user')
                ->latest()
                ->get(),
            'activityLogs' => ActivityLog::query()
                ->with('user')
                ->latest()
                ->get(),
        ]);
    }
}
