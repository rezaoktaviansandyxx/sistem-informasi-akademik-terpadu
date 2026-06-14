<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Models\GradeRecord;
use App\Models\KrsHeader;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class StudentAcademicController extends Controller
{
    public function khs()
    {
        $student = $this->resolveStudent();

        $items = GradeRecord::query()
            ->with(['academicClass.course', 'academicClass.semester.academicYear'])
            ->where('student_id', $student->id)
            ->get()
            ->map(function (GradeRecord $record): array {
                $class = $record->academicClass;
                $course = $class?->course;
                $semester = $class?->semester;
                $year = $semester?->academicYear;

                return [
                    'semester' => $semester?->name,
                    'academic_year' => $year?->label,
                    'course_code' => $course?->code,
                    'course_name' => $course?->name,
                    'credits' => $course?->credits,
                    'class_name' => $class?->name,
                    'final_numeric' => $record->final_numeric,
                    'final_letter' => $record->final_letter,
                    'status' => $record->status,
                ];
            })
            ->values();

        return ApiResponse::success([
            'student' => [
                'id' => $student->id,
                'nim' => $student->nim,
                'name' => $student->name,
                'academic_status' => $student->academic_status,
            ],
            'items' => $items,
            'summary' => [
                'ips' => $this->calculateIps($items->all()),
                'total_courses' => $items->count(),
                'total_credits' => $items->sum('credits'),
            ],
        ], 'KHS mahasiswa berhasil diambil');
    }

    public function transcript()
    {
        $student = $this->resolveStudent();
        $records = GradeRecord::query()
            ->with(['academicClass.course'])
            ->where('student_id', $student->id)
            ->whereNotNull('final_letter')
            ->get();

        $items = $records->map(function (GradeRecord $record): array {
            $course = $record->academicClass?->course;

            return [
                'course_code' => $course?->code,
                'course_name' => $course?->name,
                'credits' => $course?->credits,
                'final_numeric' => $record->final_numeric,
                'final_letter' => $record->final_letter,
                'grade_point' => $this->gradePoint((string) $record->final_letter),
            ];
        })->values();

        $totalCredits = $items->sum('credits');
        $totalPoints = $items->sum(fn (array $item): float => ((float) $item['credits']) * ((float) $item['grade_point']));

        return ApiResponse::success([
            'student' => [
                'id' => $student->id,
                'nim' => $student->nim,
                'name' => $student->name,
            ],
            'items' => $items,
            'summary' => [
                'ipk' => $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0,
                'total_credits' => $totalCredits,
                'passed_courses' => $items->count(),
            ],
        ], 'Transkrip mahasiswa berhasil diambil');
    }

    public function schedule()
    {
        $student = $this->resolveStudent();

        $draftOrSubmitted = KrsHeader::query()
            ->where('student_id', $student->id)
            ->latest()
            ->first();

        $items = collect();

        if ($draftOrSubmitted) {
            $items = DB::table('krs_details')
                ->join('academic_classes', 'academic_classes.id', '=', 'krs_details.academic_class_id')
                ->join('courses', 'courses.id', '=', 'academic_classes.course_id')
                ->leftJoin('class_schedules', 'class_schedules.academic_class_id', '=', 'academic_classes.id')
                ->leftJoin('rooms', 'rooms.id', '=', 'class_schedules.room_id')
                ->leftJoin('lecturers', 'lecturers.id', '=', 'class_schedules.lecturer_id')
                ->where('krs_details.krs_header_id', $draftOrSubmitted->id)
                ->orderBy('class_schedules.day_of_week')
                ->orderBy('class_schedules.start_time')
                ->get([
                    'academic_classes.id as class_id',
                    'academic_classes.name as class_name',
                    'courses.code as course_code',
                    'courses.name as course_name',
                    'courses.credits',
                    'class_schedules.day_of_week',
                    'class_schedules.start_time',
                    'class_schedules.end_time',
                    'rooms.code as room_code',
                    'rooms.name as room_name',
                    'lecturers.name as lecturer_name',
                ])
                ->map(fn ($row): array => [
                    'class_id' => $row->class_id,
                    'class_name' => $row->class_name,
                    'course_code' => $row->course_code,
                    'course_name' => $row->course_name,
                    'credits' => $row->credits,
                    'day' => $this->dayLabel((int) ($row->day_of_week ?? 0)),
                    'start_time' => $row->start_time,
                    'end_time' => $row->end_time,
                    'room' => trim(($row->room_code ?? '').' '.($row->room_name ?? '')),
                    'lecturer' => $row->lecturer_name,
                ])
                ->values();
        }

        return ApiResponse::success([
            'student' => [
                'id' => $student->id,
                'nim' => $student->nim,
                'name' => $student->name,
            ],
            'items' => $items,
        ], 'Jadwal kuliah mahasiswa berhasil diambil');
    }

    public function attendance()
    {
        $student = $this->resolveStudent();

        $items = DB::table('student_attendances')
            ->join('academic_classes', 'academic_classes.id', '=', 'student_attendances.academic_class_id')
            ->join('courses', 'courses.id', '=', 'academic_classes.course_id')
            ->where('student_attendances.student_id', $student->id)
            ->orderBy('courses.code')
            ->orderBy('student_attendances.meeting_no')
            ->get([
                'courses.code as course_code',
                'courses.name as course_name',
                'student_attendances.meeting_no',
                'student_attendances.status',
                'student_attendances.notes',
            ])
            ->groupBy('course_code')
            ->map(function ($rows, string $courseCode): array {
                $first = $rows->first();

                return [
                    'course_code' => $courseCode,
                    'course_name' => $first->course_name,
                    'meetings' => $rows->map(fn ($row): array => [
                        'meeting_no' => $row->meeting_no,
                        'status' => $row->status,
                        'notes' => $row->notes,
                    ])->values()->all(),
                    'summary' => [
                        'present' => $rows->where('status', 'present')->count(),
                        'excused' => $rows->where('status', 'excused')->count(),
                        'sick' => $rows->where('status', 'sick')->count(),
                        'absent' => $rows->where('status', 'absent')->count(),
                    ],
                ];
            })
            ->values();

        return ApiResponse::success([
            'student' => [
                'id' => $student->id,
                'nim' => $student->nim,
                'name' => $student->name,
                'academic_status' => $student->academic_status,
            ],
            'items' => $items,
        ], 'Presensi mahasiswa berhasil diambil');
    }

    public function status()
    {
        $student = $this->resolveStudent();

        return ApiResponse::success([
            'student' => [
                'id' => $student->id,
                'nim' => $student->nim,
                'name' => $student->name,
                'academic_status' => $student->academic_status,
            ],
            'metrics' => [
                'ips' => $this->calculateIps(
                    GradeRecord::query()
                        ->with('academicClass.course')
                        ->where('student_id', $student->id)
                        ->get()
                        ->map(fn (GradeRecord $record): array => [
                            'credits' => $record->academicClass?->course?->credits ?? 0,
                            'final_letter' => $record->final_letter,
                        ])
                        ->all()
                ),
                'krs_status' => KrsHeader::query()->where('student_id', $student->id)->latest()->value('status') ?? 'draft',
            ],
        ], 'Status akademik mahasiswa berhasil diambil');
    }

    private function resolveStudent(): Student
    {
        $studentId = request()->query('student_id');

        if (is_string($studentId) && $studentId !== '') {
            return Student::query()->findOrFail($studentId);
        }

        $user = request()->user();
        $student = Student::query()
            ->where('user_id', $user?->id)
            ->first();

        if ($student) {
            return $student;
        }

        // Admin/pimpinan dapat membuka halaman akademik mahasiswa untuk data demo
        // ketika tidak mengirimkan student_id secara eksplisit.
        return Student::query()
            ->orderBy('name')
            ->firstOrFail();
    }

    private function calculateIps(array $items): float
    {
        $totalCredits = 0;
        $totalPoints = 0.0;

        foreach ($items as $item) {
            $credits = (int) ($item['credits'] ?? 0);
            $point = $this->gradePoint((string) ($item['final_letter'] ?? ''));
            $totalCredits += $credits;
            $totalPoints += $credits * $point;
        }

        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.0;
    }

    private function gradePoint(string $letter): float
    {
        return match (strtoupper($letter)) {
            'A' => 4.0,
            'B' => 3.0,
            'C' => 2.0,
            'D' => 1.0,
            default => 0.0,
        };
    }

    private function dayLabel(int $day): string
    {
        return match ($day) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            default => 'Belum diatur',
        };
    }
}
