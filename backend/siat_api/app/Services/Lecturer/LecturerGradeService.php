<?php

namespace App\Services\Lecturer;

use App\Models\GradeRecord;

class LecturerGradeService
{
    public function list(string $classId): array
    {
        return [
            'class_id' => $classId,
            'items' => GradeRecord::query()
                ->with('student')
                ->where('academic_class_id', $classId)
                ->get()
                ->map(fn (GradeRecord $record) => [
                    'student_id' => $record->student_id,
                    'student_name' => $record->student?->name,
                    'assignment_score' => $record->assignment_score,
                    'mid_score' => $record->mid_score,
                    'final_score' => $record->final_score,
                    'final_numeric' => $record->final_numeric,
                    'status' => $record->status,
                ])
                ->values()
                ->all(),
        ];
    }

    public function upsert(string $classId, array $payload): array
    {
        foreach ($payload['grades'] ?? [] as $grade) {
            $finalNumeric = round(
                (($grade['assignment_score'] ?? 0) * 0.3) +
                (($grade['mid_score'] ?? 0) * 0.3) +
                (($grade['final_score'] ?? 0) * 0.4),
                2
            );

            GradeRecord::query()->updateOrCreate(
                [
                    'academic_class_id' => $classId,
                    'student_id' => $grade['student_id'],
                ],
                [
                    'assignment_score' => $grade['assignment_score'] ?? null,
                    'mid_score' => $grade['mid_score'] ?? null,
                    'final_score' => $grade['final_score'] ?? null,
                    'final_numeric' => $finalNumeric,
                    'status' => 'draft',
                ]
            );
        }

        return [
            'class_id' => $classId,
            'status' => 'draft_saved',
            'grades_count' => count($payload['grades'] ?? []),
        ];
    }

    public function finalize(string $classId): array
    {
        GradeRecord::query()
            ->where('academic_class_id', $classId)
            ->update([
                'status' => 'finalized',
                'finalized_at' => now(),
            ]);

        return [
            'class_id' => $classId,
            'status' => 'finalized',
            'finalized_at' => now()->toIso8601String(),
        ];
    }
}
