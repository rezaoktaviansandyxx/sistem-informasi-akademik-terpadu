<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Models\Approval;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GovernanceController extends Controller
{
    public function announcements()
    {
        $items = DB::table('announcements')
            ->orderByDesc('created_at')
            ->get();

        return ApiResponse::success(['items' => $items], 'Daftar pengumuman berhasil diambil');
    }

    public function storeAnnouncement(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['required', 'in:draft,published,archived'],
        ]);

        $payload = array_merge($validated, [
            'id' => (string) Str::uuid(),
            'published_at' => $validated['status'] === 'published' ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('announcements')->insert($payload);
        $this->logActivity('administration', 'announcement.created', $payload);

        return ApiResponse::success($payload, 'Pengumuman berhasil dibuat', null, 201);
    }

    public function calendar()
    {
        $items = DB::table('academic_calendar_events')
            ->orderBy('start_date')
            ->get();

        return ApiResponse::success(['items' => $items], 'Kalender akademik berhasil diambil');
    }

    public function storeCalendar(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:draft,published,archived'],
            'notes' => ['nullable', 'string'],
        ]);

        $payload = array_merge($validated, [
            'id' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('academic_calendar_events')->insert($payload);
        $this->logActivity('administration', 'calendar.created', $payload);

        return ApiResponse::success($payload, 'Kalender akademik berhasil dibuat', null, 201);
    }

    public function letters()
    {
        $items = DB::table('academic_letters')
            ->join('students', 'students.id', '=', 'academic_letters.student_id')
            ->orderByDesc('academic_letters.created_at')
            ->get([
                'academic_letters.*',
                'students.nim',
                'students.name as student_name',
            ]);

        return ApiResponse::success(['items' => $items], 'Surat akademik berhasil diambil');
    }

    public function storeLetter(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'uuid', 'exists:students,id'],
            'type' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:requested,verified,approved,rejected,issued'],
            'notes' => ['nullable', 'string'],
        ]);

        $payload = array_merge($validated, [
            'id' => (string) Str::uuid(),
            'requested_at' => now(),
            'processed_at' => in_array($validated['status'], ['approved', 'issued', 'rejected', 'verified'], true) ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('academic_letters')->insert($payload);
        $this->logActivity('administration', 'letter.created', $payload);

        return ApiResponse::success($payload, 'Surat akademik berhasil dibuat', null, 201);
    }

    public function verifications()
    {
        $items = DB::table('verification_records')
            ->orderByDesc('created_at')
            ->get();

        return ApiResponse::success(['items' => $items], 'Daftar verifikasi data berhasil diambil');
    }

    public function storeVerification(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'max:100'],
            'subject_type' => ['required', 'string', 'max:100'],
            'subject_id' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:pending,approved,rejected'],
            'old_payload' => ['nullable', 'array'],
            'new_payload' => ['nullable', 'array'],
            'evidence_url' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $payload = array_merge($validated, [
            'id' => (string) Str::uuid(),
            'old_payload' => isset($validated['old_payload']) ? json_encode($validated['old_payload']) : null,
            'new_payload' => isset($validated['new_payload']) ? json_encode($validated['new_payload']) : null,
            'verified_at' => $validated['status'] !== 'pending' ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('verification_records')->insert($payload);
        $this->logActivity('administration', 'verification.created', $payload);

        return ApiResponse::success($payload, 'Verifikasi data berhasil dibuat', null, 201);
    }

    public function auditTrail()
    {
        $items = DB::table('audit_logs')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return ApiResponse::success(['items' => $items], 'Audit trail berhasil diambil');
    }

    public function activityLogs()
    {
        $items = DB::table('activity_logs')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        return ApiResponse::success(['items' => $items], 'Activity log berhasil diambil');
    }

    public function reports()
    {
        $report = $this->reportSummary();

        return ApiResponse::success([
            'summary' => [
                'students' => $report['students'],
                'lecturers' => $report['lecturers'],
                'approvals_pending' => $report['approvals_pending'],
                'letters_requested' => $report['letters_requested'],
                'published_announcements' => $report['published_announcements'],
                'average_final_grade' => $report['average_final_grade'],
                'attendance_rate' => $report['attendance_rate'],
            ],
            'highlights' => [
                [
                    'label' => 'Kepatuhan Presensi',
                    'value' => $report['attendance_rate'].'%',
                    'tone' => $report['attendance_rate'] >= 90 ? 'positive' : 'warning',
                ],
                [
                    'label' => 'SLA Approval',
                    'value' => $report['approvals_pending'] <= 10 ? 'On Track' : 'Padat',
                    'tone' => $report['approvals_pending'] <= 10 ? 'positive' : 'warning',
                ],
            ],
            'exports' => [
                [
                    'type' => 'pdf',
                    'name' => 'Executive Summary PDF',
                    'status' => 'ready',
                    'download_url' => '/reports/export/pdf',
                ],
                [
                    'type' => 'excel',
                    'name' => 'Rekap Operasional Excel',
                    'status' => 'ready',
                    'download_url' => '/reports/export/excel',
                ],
            ],
        ], 'Ringkasan laporan berhasil diambil');
    }

    public function export(string $format)
    {
        $report = $this->reportSummary();
        $timestamp = now()->format('Ymd-His');

        if ($format === 'excel') {
            $headers = ['Metrik', 'Nilai'];
            $rows = [
                ['Mahasiswa', $report['students']],
                ['Dosen', $report['lecturers']],
                ['Approval Pending', $report['approvals_pending']],
                ['Surat Requested', $report['letters_requested']],
                ['Pengumuman Aktif', $report['published_announcements']],
                ['Rata-rata Nilai Akhir', $report['average_final_grade']],
                ['Tingkat Kehadiran', $report['attendance_rate'].'%'],
            ];

            return response()->streamDownload(function () use ($headers, $rows): void {
                $handle = fopen('php://output', 'wb');
                fputcsv($handle, $headers);
                foreach ($rows as $row) {
                    fputcsv($handle, $row);
                }
                fclose($handle);
            }, "laporan-operasional-{$timestamp}.csv", [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        if ($format === 'pdf') {
            $pdf = $this->simplePdf([
                'Laporan Eksekutif SIAT',
                'Tanggal: '.now()->format('d M Y H:i'),
                '',
                'Mahasiswa Aktif: '.$report['students'],
                'Dosen Aktif: '.$report['lecturers'],
                'Approval Pending: '.$report['approvals_pending'],
                'Surat Requested: '.$report['letters_requested'],
                'Pengumuman Aktif: '.$report['published_announcements'],
                'Rata-rata Nilai Akhir: '.$report['average_final_grade'],
                'Kehadiran Mahasiswa: '.$report['attendance_rate'].'%',
            ]);

            return response($pdf, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=laporan-eksekutif-{$timestamp}.pdf",
            ]);
        }

        return ApiResponse::error(
            'Format ekspor tidak didukung.',
            ['format' => ['Gunakan format `pdf` atau `excel`.']],
            422
        );
    }

    private function logActivity(string $module, string $action, array $context): void
    {
        DB::table('activity_logs')->insert([
            'user_id' => request()->user()?->id,
            'module' => $module,
            'action' => $action,
            'context' => json_encode($context),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function reportSummary(): array
    {
        $studentCount = DB::table('students')->count();
        $lecturerCount = DB::table('lecturers')->count();
        $approvalPending = Approval::query()->where('status', 'pending')->count();
        $letterRequested = DB::table('academic_letters')->where('status', 'requested')->count();
        $publishedAnnouncements = DB::table('announcements')->where('status', 'published')->count();
        $averageGrade = DB::table('grade_records')->avg('final_numeric');
        $totalAttendances = DB::table('student_attendances')->count();
        $presentAttendances = DB::table('student_attendances')->where('status', 'present')->count();

        return [
            'students' => $studentCount,
            'lecturers' => $lecturerCount,
            'approvals_pending' => $approvalPending,
            'letters_requested' => $letterRequested,
            'published_announcements' => $publishedAnnouncements,
            'average_final_grade' => round((float) $averageGrade, 2),
            'attendance_rate' => $totalAttendances > 0
                ? round(($presentAttendances / $totalAttendances) * 100, 2)
                : 0.0,
        ];
    }

    private function simplePdf(array $lines): string
    {
        $escapedLines = array_map(function (string $line): string {
            return str_replace(
                ['\\', '(', ')'],
                ['\\\\', '\\(', '\\)'],
                $line
            );
        }, $lines);

        $content = "BT\n/F1 14 Tf\n50 780 Td\n";
        foreach ($escapedLines as $index => $line) {
            if ($index > 0) {
                $content .= "0 -20 Td\n";
            }
            $content .= "({$line}) Tj\n";
        }
        $content .= "ET";

        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj",
            "2 0 obj\n<< /Type /Pages /Count 1 /Kids [3 0 R] >>\nendobj",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj",
            "5 0 obj\n<< /Length ".strlen($content)." >>\nstream\n{$content}\nendstream\nendobj",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object."\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= str_pad((string) $offset, 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }
}
