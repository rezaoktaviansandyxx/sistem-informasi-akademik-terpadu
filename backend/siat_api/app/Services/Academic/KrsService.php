<?php

namespace App\Services\Academic;

use App\Models\AcademicClass;
use App\Models\KrsDetail;
use App\Models\KrsHeader;
use App\Models\Student;
use App\Repositories\Contracts\KrsRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class KrsService
{
    public function __construct(
        private readonly KrsRepositoryInterface $krsRepository
    ) {
    }

    public function current(string $studentId): array
    {
        return $this->krsRepository->currentDraft($studentId);
    }

    public function addEntry(array $payload): array
    {
        $student = Student::query()->findOrFail($payload['student_id']);
        $class = AcademicClass::query()
            ->with('course')
            ->findOrFail($payload['class_id']);

        $draft = KrsHeader::query()->firstOrCreate(
            [
                'student_id' => $payload['student_id'],
                'semester_id' => $payload['semester_id'],
            ],
            [
                'status' => 'draft',
            ]
        );

        $existingCredits = KrsDetail::query()
            ->join('academic_classes', 'academic_classes.id', '=', 'krs_details.academic_class_id')
            ->join('courses', 'courses.id', '=', 'academic_classes.course_id')
            ->where('krs_header_id', $draft->id)
            ->sum('courses.credits');

        $requestedCredits = $existingCredits + (int) ($class->course?->credits ?? 0);

        if ($requestedCredits > 24) {
            throw ValidationException::withMessages([
                'class_id' => ['Total SKS melebihi batas maksimum 24 SKS.'],
            ]);
        }

        $currentEnrolled = KrsDetail::query()
            ->where('academic_class_id', $class->id)
            ->count();

        if ($currentEnrolled >= $class->capacity) {
            throw ValidationException::withMessages([
                'class_id' => ['Kapasitas kelas sudah penuh.'],
            ]);
        }

        if ($this->hasScheduleConflict($draft->id, $class->id)) {
            throw ValidationException::withMessages([
                'class_id' => ['Jadwal bentrok dengan kelas lain di draft KRS.'],
            ]);
        }

        if ($this->hasUnmetPrerequisite($student->id, $class->course_id)) {
            throw ValidationException::withMessages([
                'class_id' => ['Prasyarat mata kuliah belum terpenuhi.'],
            ]);
        }

        return $this->krsRepository->storeDraftEntry($payload);
    }

    public function submit(string $studentId): array
    {
        $draft = KrsHeader::query()
            ->where('student_id', $studentId)
            ->where('status', 'draft')
            ->latest()
            ->first();

        if ($draft) {
            $draft->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);
        }

        return [
            'student_id' => $studentId,
            'status' => $draft?->status ?? 'submitted',
            'submitted_at' => $draft?->submitted_at?->toIso8601String() ?? now()->toIso8601String(),
        ];
    }

    private function hasScheduleConflict(string $krsHeaderId, string $classId): bool
    {
        $newSchedules = DB::table('class_schedules')
            ->where('academic_class_id', $classId)
            ->get();

        if ($newSchedules->isEmpty()) {
            return false;
        }

        $existingSchedules = DB::table('krs_details')
            ->join('class_schedules', 'class_schedules.academic_class_id', '=', 'krs_details.academic_class_id')
            ->where('krs_details.krs_header_id', $krsHeaderId)
            ->get([
                'class_schedules.day_of_week',
                'class_schedules.start_time',
                'class_schedules.end_time',
            ]);

        foreach ($newSchedules as $newSchedule) {
            foreach ($existingSchedules as $existingSchedule) {
                if ((int) $newSchedule->day_of_week !== (int) $existingSchedule->day_of_week) {
                    continue;
                }

                if ($newSchedule->start_time < $existingSchedule->end_time
                    && $newSchedule->end_time > $existingSchedule->start_time) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasUnmetPrerequisite(string $studentId, string $courseId): bool
    {
        $prerequisiteIds = DB::table('course_prerequisites')
            ->where('course_id', $courseId)
            ->pluck('prerequisite_course_id')
            ->all();

        if ($prerequisiteIds === []) {
            return false;
        }

        $passedCourseIds = DB::table('grade_records')
            ->join('academic_classes', 'academic_classes.id', '=', 'grade_records.academic_class_id')
            ->where('grade_records.student_id', $studentId)
            ->whereIn('grade_records.final_letter', ['A', 'B', 'C'])
            ->pluck('academic_classes.course_id')
            ->all();

        foreach ($prerequisiteIds as $prerequisiteId) {
            if (! in_array($prerequisiteId, $passedCourseIds, true)) {
                return true;
            }
        }

        return false;
    }
}
