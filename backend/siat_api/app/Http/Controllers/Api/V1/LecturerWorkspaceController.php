<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LecturerWorkspaceController extends Controller
{
    public function classes()
    {
        $lecturer = $this->resolveLecturer();

        $items = DB::table('class_schedules')
            ->join('academic_classes', 'academic_classes.id', '=', 'class_schedules.academic_class_id')
            ->join('courses', 'courses.id', '=', 'academic_classes.course_id')
            ->join('semesters', 'semesters.id', '=', 'academic_classes.semester_id')
            ->leftJoin('rooms', 'rooms.id', '=', 'class_schedules.room_id')
            ->where('class_schedules.lecturer_id', $lecturer->id)
            ->orderBy('courses.code')
            ->get([
                'academic_classes.id as class_id',
                'academic_classes.name as class_name',
                'courses.code as course_code',
                'courses.name as course_name',
                'courses.credits',
                'semesters.name as semester_name',
                'class_schedules.day_of_week',
                'class_schedules.start_time',
                'class_schedules.end_time',
                'rooms.code as room_code',
            ])
            ->map(fn ($row): array => [
                'class_id' => $row->class_id,
                'class_name' => $row->class_name,
                'course_code' => $row->course_code,
                'course_name' => $row->course_name,
                'credits' => $row->credits,
                'semester' => $row->semester_name,
                'day_of_week' => $row->day_of_week,
                'start_time' => $row->start_time,
                'end_time' => $row->end_time,
                'room_code' => $row->room_code,
            ])
            ->values();

        return ApiResponse::success([
            'lecturer' => [
                'id' => $lecturer->id,
                'name' => $lecturer->name,
                'nidn' => $lecturer->nidn,
            ],
            'items' => $items,
            'summary' => [
                'classes_count' => $items->count(),
                'total_credits' => $items->sum('credits'),
            ],
        ], 'Daftar kelas dosen berhasil diambil');
    }

    public function teachingAttendances()
    {
        $lecturer = $this->resolveLecturer();

        $items = DB::table('teaching_attendances')
            ->join('academic_classes', 'academic_classes.id', '=', 'teaching_attendances.academic_class_id')
            ->join('courses', 'courses.id', '=', 'academic_classes.course_id')
            ->where('teaching_attendances.lecturer_id', $lecturer->id)
            ->orderBy('teaching_attendances.held_on')
            ->get([
                'teaching_attendances.id',
                'courses.code as course_code',
                'courses.name as course_name',
                'teaching_attendances.meeting_no',
                'teaching_attendances.topic',
                'teaching_attendances.held_on',
                'teaching_attendances.status',
            ]);

        return ApiResponse::success([
            'lecturer' => [
                'id' => $lecturer->id,
                'name' => $lecturer->name,
            ],
            'items' => $items,
        ], 'Presensi mengajar dosen berhasil diambil');
    }

    public function storeTeachingAttendance(Request $request)
    {
        $lecturer = $this->resolveLecturer();
        $validated = $request->validate([
            'academic_class_id' => ['required', 'uuid', 'exists:academic_classes,id'],
            'meeting_no' => ['required', 'integer', 'min:1', 'max:16'],
            'topic' => ['required', 'string', 'max:255'],
            'held_on' => ['required', 'date'],
            'status' => ['required', 'in:held,rescheduled,cancelled'],
        ]);

        $payload = array_merge($validated, [
            'id' => (string) Str::uuid(),
            'lecturer_id' => $lecturer->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('teaching_attendances')->insert($payload);

        return ApiResponse::success($payload, 'Presensi mengajar berhasil dicatat', null, 201);
    }

    public function teachingSummary()
    {
        $lecturer = $this->resolveLecturer();
        $classes = $this->classes()->getData(true)['data']['items'] ?? [];
        $attendances = DB::table('teaching_attendances')
            ->where('lecturer_id', $lecturer->id)
            ->count();

        return ApiResponse::success([
            'lecturer' => [
                'id' => $lecturer->id,
                'name' => $lecturer->name,
            ],
            'summary' => [
                'active_classes' => count($classes),
                'meetings_recorded' => $attendances,
                'teaching_load_credits' => collect($classes)->sum('credits'),
            ],
        ], 'Rekap mengajar dosen berhasil diambil');
    }

    private function resolveLecturer(): Lecturer
    {
        $lecturerId = request()->query('lecturer_id');

        if (is_string($lecturerId) && $lecturerId !== '') {
            return Lecturer::query()->findOrFail($lecturerId);
        }

        return Lecturer::query()
            ->where('user_id', request()->user()?->id)
            ->firstOrFail();
    }
}
