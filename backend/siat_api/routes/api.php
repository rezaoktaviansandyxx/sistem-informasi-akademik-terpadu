<?php

use App\Http\Controllers\Api\V1\ApprovalController;
use App\Http\Controllers\Api\V1\AcademicClassController;
use App\Http\Controllers\Api\V1\AcademicYearController;
use App\Http\Controllers\Api\V1\AccessManagementController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\FacultyController;
use App\Http\Controllers\Api\V1\GovernanceController;
use App\Http\Controllers\Api\V1\KrsController;
use App\Http\Controllers\Api\V1\LecturerController;
use App\Http\Controllers\Api\V1\LecturerGradeController;
use App\Http\Controllers\Api\V1\LecturerWorkspaceController;
use App\Http\Controllers\Api\V1\ReferenceController;
use App\Http\Controllers\Api\V1\SemesterController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\StudentAcademicController;
use App\Http\Controllers\Api\V1\StudyProgramController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
        Route::get('/auth/sessions', [AuthController::class, 'sessions']);
        Route::post('/auth/logout-other-sessions', [AuthController::class, 'logoutOtherSessions']);

        Route::get('/dashboard', DashboardController::class);

        Route::middleware('role:student,admin')->group(function () {
            Route::get('/krs/current', [KrsController::class, 'current']);
            Route::get('/krs/catalog', [KrsController::class, 'catalog']);
            Route::post('/krs/entries', [KrsController::class, 'storeEntry']);
            Route::post('/krs/submit', [KrsController::class, 'submit']);
            Route::get('/student/khs', [StudentAcademicController::class, 'khs']);
            Route::get('/student/transcript', [StudentAcademicController::class, 'transcript']);
            Route::get('/student/schedule', [StudentAcademicController::class, 'schedule']);
            Route::get('/student/attendance', [StudentAcademicController::class, 'attendance']);
            Route::get('/student/status', [StudentAcademicController::class, 'status']);
        });

        Route::middleware('role:lecturer,admin')->group(function () {
            Route::get('/lecturer/classes/{classId}/grades', [LecturerGradeController::class, 'index']);
            Route::put('/lecturer/classes/{classId}/grades', [LecturerGradeController::class, 'update']);
            Route::post('/lecturer/classes/{classId}/grades/finalize', [LecturerGradeController::class, 'finalize']);
            Route::get('/lecturer/classes', [LecturerWorkspaceController::class, 'classes']);
            Route::get('/lecturer/attendances', [LecturerWorkspaceController::class, 'teachingAttendances']);
            Route::post('/lecturer/attendances', [LecturerWorkspaceController::class, 'storeTeachingAttendance']);
            Route::get('/lecturer/teaching-summary', [LecturerWorkspaceController::class, 'teachingSummary']);
        });

        Route::middleware('role:admin,leader')->group(function () {
            Route::get('/approvals', [ApprovalController::class, 'index']);
            Route::post('/approvals/{approvalId}/decision', [ApprovalController::class, 'decide']);
            Route::get('/announcements', [GovernanceController::class, 'announcements']);
            Route::post('/announcements', [GovernanceController::class, 'storeAnnouncement']);
            Route::get('/academic-calendar', [GovernanceController::class, 'calendar']);
            Route::post('/academic-calendar', [GovernanceController::class, 'storeCalendar']);
            Route::get('/academic-letters', [GovernanceController::class, 'letters']);
            Route::post('/academic-letters', [GovernanceController::class, 'storeLetter']);
            Route::get('/verifications', [GovernanceController::class, 'verifications']);
            Route::post('/verifications', [GovernanceController::class, 'storeVerification']);
            Route::get('/audit-trail', [GovernanceController::class, 'auditTrail']);
            Route::get('/activity-logs', [GovernanceController::class, 'activityLogs']);
            Route::get('/reports', [GovernanceController::class, 'reports']);
            Route::get('/reports/export/{format}', [GovernanceController::class, 'export']);
        });

        Route::prefix('master')->middleware('role:admin')->group(function () {
            Route::get('/faculties', [FacultyController::class, 'index']);
            Route::post('/faculties', [FacultyController::class, 'store']);
            Route::get('/faculties/{faculty}', [FacultyController::class, 'show']);
            Route::put('/faculties/{faculty}', [FacultyController::class, 'update']);
            Route::delete('/faculties/{faculty}', [FacultyController::class, 'destroy']);

            Route::get('/study-programs', [StudyProgramController::class, 'index']);
            Route::post('/study-programs', [StudyProgramController::class, 'store']);
            Route::get('/study-programs/{studyProgram}', [StudyProgramController::class, 'show']);
            Route::put('/study-programs/{studyProgram}', [StudyProgramController::class, 'update']);
            Route::delete('/study-programs/{studyProgram}', [StudyProgramController::class, 'destroy']);

            Route::get('/academic-years', [AcademicYearController::class, 'index']);
            Route::post('/academic-years', [AcademicYearController::class, 'store']);
            Route::get('/academic-years/{academicYear}', [AcademicYearController::class, 'show']);
            Route::put('/academic-years/{academicYear}', [AcademicYearController::class, 'update']);
            Route::delete('/academic-years/{academicYear}', [AcademicYearController::class, 'destroy']);

            Route::get('/semesters', [SemesterController::class, 'index']);
            Route::post('/semesters', [SemesterController::class, 'store']);
            Route::get('/semesters/{semester}', [SemesterController::class, 'show']);
            Route::put('/semesters/{semester}', [SemesterController::class, 'update']);
            Route::delete('/semesters/{semester}', [SemesterController::class, 'destroy']);

            Route::get('/students', [StudentController::class, 'index']);
            Route::post('/students', [StudentController::class, 'store']);
            Route::get('/students/{student}', [StudentController::class, 'show']);
            Route::put('/students/{student}', [StudentController::class, 'update']);
            Route::delete('/students/{student}', [StudentController::class, 'destroy']);

            Route::get('/lecturers', [LecturerController::class, 'index']);
            Route::post('/lecturers', [LecturerController::class, 'store']);
            Route::get('/lecturers/{lecturer}', [LecturerController::class, 'show']);
            Route::put('/lecturers/{lecturer}', [LecturerController::class, 'update']);
            Route::delete('/lecturers/{lecturer}', [LecturerController::class, 'destroy']);

            Route::get('/courses', [CourseController::class, 'index']);
            Route::post('/courses', [CourseController::class, 'store']);
            Route::get('/courses/{course}', [CourseController::class, 'show']);
            Route::put('/courses/{course}', [CourseController::class, 'update']);
            Route::delete('/courses/{course}', [CourseController::class, 'destroy']);

            Route::get('/academic-classes', [AcademicClassController::class, 'index']);
            Route::post('/academic-classes', [AcademicClassController::class, 'store']);
            Route::get('/academic-classes/{academicClass}', [AcademicClassController::class, 'show']);
            Route::put('/academic-classes/{academicClass}', [AcademicClassController::class, 'update']);
            Route::delete('/academic-classes/{academicClass}', [AcademicClassController::class, 'destroy']);

            Route::get('/departments', [ReferenceController::class, 'departments']);
            Route::post('/departments', [ReferenceController::class, 'storeDepartment']);
            Route::get('/curricula', [ReferenceController::class, 'curricula']);
            Route::post('/curricula', [ReferenceController::class, 'storeCurriculum']);
            Route::get('/rooms', [ReferenceController::class, 'rooms']);
            Route::post('/rooms', [ReferenceController::class, 'storeRoom']);
            Route::get('/schedules', [ReferenceController::class, 'schedules']);
            Route::post('/schedules', [ReferenceController::class, 'storeSchedule']);
        });

        Route::prefix('security')->middleware('role:super_admin,admin')->group(function () {
            Route::get('/users', [AccessManagementController::class, 'users']);
            Route::put('/users/{user}/roles', [AccessManagementController::class, 'assignRoles']);
            Route::get('/roles', [AccessManagementController::class, 'roles']);
            Route::get('/permissions', [AccessManagementController::class, 'permissions']);
            Route::get('/settings', [AccessManagementController::class, 'settings']);
            Route::post('/settings', [AccessManagementController::class, 'upsertSetting']);
        });
    });
});
