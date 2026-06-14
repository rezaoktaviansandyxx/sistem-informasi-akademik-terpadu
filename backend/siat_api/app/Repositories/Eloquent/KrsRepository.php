<?php

namespace App\Repositories\Eloquent;

use App\Models\KrsHeader;
use App\Models\Semester;
use App\Repositories\Contracts\KrsRepositoryInterface;
use Illuminate\Support\Str;

class KrsRepository implements KrsRepositoryInterface
{
    public function currentDraft(string $studentId): array
    {
        $semester = Semester::query()->where('is_active', true)->first();

        $draft = KrsHeader::query()
            ->with(['details.academicClass.course', 'semester'])
            ->where('student_id', $studentId)
            ->when($semester, fn ($query) => $query->where('semester_id', $semester->id))
            ->latest()
            ->first();

        if (! $draft) {
            return [
                'student_id' => $studentId,
                'semester' => $semester?->name,
                'status' => 'draft',
                'entries' => [],
            ];
        }

        return [
            'student_id' => $studentId,
            'semester' => $draft->semester?->name,
            'status' => $draft->status,
            'entries' => $draft->details->map(function ($detail) {
                return [
                    'class_id' => $detail->academic_class_id,
                    'course_code' => $detail->academicClass?->course?->code,
                    'course_name' => $detail->academicClass?->course?->name,
                    'credits' => $detail->academicClass?->course?->credits,
                ];
            })->values()->all(),
        ];
    }

    public function storeDraftEntry(array $payload): array
    {
        $draft = KrsHeader::query()->firstOrCreate(
            [
                'student_id' => $payload['student_id'],
                'semester_id' => $payload['semester_id'],
            ],
            [
                'id' => (string) Str::uuid(),
                'status' => 'draft',
            ]
        );

        $detail = $draft->details()->firstOrCreate(
            [
                'academic_class_id' => $payload['class_id'],
            ],
            [
                'id' => (string) Str::uuid(),
            ]
        );

        return [
            'student_id' => $draft->student_id,
            'class_id' => $detail->academic_class_id,
            'semester_id' => $draft->semester_id,
            'status' => 'draft_added',
        ];
    }
}
