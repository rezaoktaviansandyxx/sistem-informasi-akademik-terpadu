<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferenceController extends Controller
{
    public function departments()
    {
        $items = DB::table('departments')
            ->join('faculties', 'faculties.id', '=', 'departments.faculty_id')
            ->orderBy('departments.name')
            ->get([
                'departments.*',
                'faculties.name as faculty_name',
            ]);

        return ApiResponse::success(['items' => $items], 'Daftar jurusan berhasil diambil');
    }

    public function storeDepartment(Request $request)
    {
        $validated = $request->validate([
            'faculty_id' => ['required', 'uuid', 'exists:faculties,id'],
            'code' => ['required', 'string', 'max:20', 'unique:departments,code'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $payload = array_merge($validated, [
            'id' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('departments')->insert($payload);

        return ApiResponse::success($payload, 'Jurusan berhasil dibuat', null, 201);
    }

    public function curricula()
    {
        $items = DB::table('curricula')
            ->join('study_programs', 'study_programs.id', '=', 'curricula.study_program_id')
            ->orderBy('curricula.name')
            ->get([
                'curricula.*',
                'study_programs.code as study_program_code',
                'study_programs.name as study_program_name',
            ]);

        return ApiResponse::success(['items' => $items], 'Daftar kurikulum berhasil diambil');
    }

    public function storeCurriculum(Request $request)
    {
        $validated = $request->validate([
            'study_program_id' => ['required', 'uuid', 'exists:study_programs,id'],
            'code' => ['required', 'string', 'max:20', 'unique:curricula,code'],
            'name' => ['required', 'string', 'max:255'],
            'total_credits' => ['required', 'integer', 'min:1', 'max:200'],
            'is_active' => ['required', 'boolean'],
        ]);

        $payload = array_merge($validated, [
            'id' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('curricula')->insert($payload);

        return ApiResponse::success($payload, 'Kurikulum berhasil dibuat', null, 201);
    }

    public function rooms()
    {
        $items = DB::table('rooms')
            ->orderBy('code')
            ->get();

        return ApiResponse::success(['items' => $items], 'Daftar ruangan berhasil diambil');
    }

    public function storeRoom(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:rooms,code'],
            'name' => ['required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        $payload = array_merge($validated, [
            'id' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('rooms')->insert($payload);

        return ApiResponse::success($payload, 'Ruangan berhasil dibuat', null, 201);
    }

    public function schedules()
    {
        $items = DB::table('class_schedules')
            ->join('academic_classes', 'academic_classes.id', '=', 'class_schedules.academic_class_id')
            ->join('courses', 'courses.id', '=', 'academic_classes.course_id')
            ->leftJoin('lecturers', 'lecturers.id', '=', 'class_schedules.lecturer_id')
            ->leftJoin('rooms', 'rooms.id', '=', 'class_schedules.room_id')
            ->orderBy('class_schedules.day_of_week')
            ->orderBy('class_schedules.start_time')
            ->get([
                'class_schedules.*',
                'academic_classes.name as class_name',
                'courses.code as course_code',
                'courses.name as course_name',
                'lecturers.name as lecturer_name',
                'rooms.code as room_code',
            ]);

        return ApiResponse::success(['items' => $items], 'Daftar jadwal kuliah berhasil diambil');
    }

    public function storeSchedule(Request $request)
    {
        $validated = $request->validate([
            'academic_class_id' => ['required', 'uuid', 'exists:academic_classes,id'],
            'lecturer_id' => ['nullable', 'uuid', 'exists:lecturers,id'],
            'room_id' => ['nullable', 'uuid', 'exists:rooms,id'],
            'day_of_week' => ['required', 'integer', 'min:1', 'max:6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $conflicts = $this->findScheduleConflicts($validated);

        if ($conflicts['room_conflict'] || $conflicts['lecturer_conflict']) {
            return ApiResponse::error('Konflik jadwal terdeteksi.', $conflicts, 422);
        }

        $payload = array_merge($validated, [
            'id' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('class_schedules')->insert($payload);

        return ApiResponse::success($payload, 'Jadwal kuliah berhasil dibuat', null, 201);
    }

    private function findScheduleConflicts(array $validated): array
    {
        $timeOverlap = function ($query) use ($validated): void {
            $query->where('day_of_week', $validated['day_of_week'])
                ->where('start_time', '<', $validated['end_time'])
                ->where('end_time', '>', $validated['start_time']);
        };

        $roomConflict = false;
        if (! empty($validated['room_id'])) {
            $roomConflict = DB::table('class_schedules')
                ->where('room_id', $validated['room_id'])
                ->where($timeOverlap)
                ->exists();
        }

        $lecturerConflict = false;
        if (! empty($validated['lecturer_id'])) {
            $lecturerConflict = DB::table('class_schedules')
                ->where('lecturer_id', $validated['lecturer_id'])
                ->where($timeOverlap)
                ->exists();
        }

        return [
            'room_conflict' => $roomConflict,
            'lecturer_conflict' => $lecturerConflict,
        ];
    }
}
