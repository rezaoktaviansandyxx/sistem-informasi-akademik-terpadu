<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Academic\StoreKrsEntryRequest;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use App\Services\Academic\KrsService;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KrsController extends Controller
{
    public function __construct(
        private readonly KrsService $krsService
    ) {
    }

    public function current(Request $request)
    {
        $student = $this->resolveStudent($request);

        return ApiResponse::success(
            $this->krsService->current($student->id),
            'Draft KRS semester aktif berhasil diambil'
        );
    }

    public function catalog(Request $request)
    {
        $student = $this->resolveStudent($request);
        $semester = $this->resolveSemester($request);
        $draft = $this->krsService->current($student->id);
        $selectedClassIds = collect($draft['entries'] ?? [])
            ->pluck('class_id')
            ->filter()
            ->values()
            ->all();

        $items = DB::table('academic_classes')
            ->join('courses', 'courses.id', '=', 'academic_classes.course_id')
            ->leftJoin('class_schedules', 'class_schedules.academic_class_id', '=', 'academic_classes.id')
            ->leftJoin('lecturers', 'lecturers.id', '=', 'class_schedules.lecturer_id')
            ->leftJoin('rooms', 'rooms.id', '=', 'class_schedules.room_id')
            ->where('academic_classes.semester_id', $semester->id)
            ->select([
                'academic_classes.id as class_id',
                'academic_classes.name as class_name',
                'academic_classes.capacity',
                'courses.id as course_id',
                'courses.code as course_code',
                'courses.name as course_name',
                'courses.credits',
                'lecturers.name as lecturer_name',
                'rooms.code as room_code',
                'class_schedules.day_of_week',
                'class_schedules.start_time',
                'class_schedules.end_time',
            ])
            ->orderBy('courses.code')
            ->get()
            ->groupBy('class_id')
            ->map(function ($rows, string $classId) use ($selectedClassIds): array {
                $firstRow = $rows->first();
                $enrolled = DB::table('krs_details')
                    ->where('academic_class_id', $classId)
                    ->count();

                return [
                    'class_id' => $classId,
                    'class_name' => $firstRow?->class_name,
                    'course_id' => $firstRow?->course_id,
                    'course_code' => $firstRow?->course_code,
                    'course_name' => $firstRow?->course_name,
                    'credits' => $firstRow?->credits,
                    'capacity' => $firstRow?->capacity,
                    'enrolled' => $enrolled,
                    'available_seats' => max((int) ($firstRow?->capacity ?? 0) - $enrolled, 0),
                    'lecturer_name' => $firstRow?->lecturer_name,
                    'room_code' => $firstRow?->room_code,
                    'schedules' => $rows
                        ->filter(fn ($row) => $row->day_of_week !== null)
                        ->map(fn ($row): array => [
                            'day_of_week' => (int) $row->day_of_week,
                            'start_time' => $row->start_time,
                            'end_time' => $row->end_time,
                        ])
                        ->values()
                        ->all(),
                    'selected' => in_array($classId, $selectedClassIds, true),
                ];
            })
            ->values()
            ->all();

        return ApiResponse::success([
            'student' => [
                'id' => $student->id,
                'nim' => $student->nim,
                'name' => $student->name,
            ],
            'semester' => [
                'id' => $semester->id,
                'name' => $semester->name,
            ],
            'items' => $items,
        ], 'Katalog kelas untuk KRS berhasil diambil');
    }

    public function storeEntry(StoreKrsEntryRequest $request)
    {
        $student = $this->resolveStudent($request);
        $semester = $this->resolveSemester($request);

        return ApiResponse::success(
            $this->krsService->addEntry(array_merge($request->validated(), [
                'student_id' => $student->id,
                'semester_id' => $semester->id,
            ])),
            'Mata kuliah berhasil ditambahkan ke draft KRS',
            null,
            201
        );
    }

    public function submit(Request $request)
    {
        $student = $this->resolveStudent($request);

        return ApiResponse::success(
            $this->krsService->submit($student->id),
            'KRS berhasil disubmit'
        );
    }

    private function resolveStudent(Request $request): Student
    {
        $studentId = (string) $request->input('student_id', $request->query('student_id', ''));
        $user = $request->user();

        if ($user instanceof User && $this->canManageOtherAcademicRecords($user->roles())) {
            if ($studentId !== '') {
                return Student::query()->findOrFail($studentId);
            }

            return Student::query()
                ->orderBy('name')
                ->firstOrFail();
        }

        return Student::query()
            ->where('user_id', $user?->id)
            ->firstOrFail();
    }

    private function resolveSemester(Request $request): Semester
    {
        $semesterId = (string) $request->input('semester_id', '');

        if ($semesterId !== '') {
            return Semester::query()->findOrFail($semesterId);
        }

        return Semester::query()
            ->where('is_active', true)
            ->firstOrFail();
    }

    private function canManageOtherAcademicRecords(?BelongsToMany $rolesQuery): bool
    {
        return $rolesQuery?->whereIn('code', ['admin'])->exists() ?? false;
    }
}
