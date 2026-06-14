<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MasterData\UpsertStudentRequest;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Http\Resources\Api\V1\StudentResource;
use App\Models\Student;
use App\Support\AppliesListSorting;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    use AppliesListSorting;

    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 15), 100);
        $search = (string) $request->query('search', '');
        $studyProgramId = (string) $request->query('study_program_id', '');
        $academicStatus = (string) $request->query('academic_status', '');
        $sortBy = $request->query('sort_by');
        $sortDirection = $request->query('sort_direction');

        $query = Student::query()
            ->with(['user', 'studyProgram.faculty'])
            ->when($studyProgramId !== '', function ($query) use ($studyProgramId): void {
                $query->where('study_program_id', $studyProgramId);
            })
            ->when($academicStatus !== '', function ($query) use ($academicStatus): void {
                $query->where('academic_status', $academicStatus);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($studentQuery) use ($search): void {
                    $studentQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('nim', 'like', "%{$search}%");
                });
            });

        $students = $this->applySorting(
            $query,
            is_string($sortBy) ? $sortBy : null,
            is_string($sortDirection) ? $sortDirection : null,
            ['name', 'nim', 'academic_status', 'created_at'],
            'name'
        )
            ->paginate($perPage)
            ->withQueryString();

        return ApiResponse::success(
            [
                'items' => $students->getCollection()
                    ->map(fn (Student $student): array => (new StudentResource($student))->resolve())
                    ->all(),
                'pagination' => [
                    'current_page' => $students->currentPage(),
                    'per_page' => $students->perPage(),
                    'total' => $students->total(),
                    'last_page' => $students->lastPage(),
                ],
            ],
            'Daftar mahasiswa berhasil diambil'
        );
    }

    public function store(UpsertStudentRequest $request)
    {
        $student = Student::query()->create($request->validated());
        $student->load(['user', 'studyProgram.faculty']);

        return ApiResponse::success(
            (new StudentResource($student))->resolve(),
            'Mahasiswa berhasil dibuat',
            null,
            201
        );
    }

    public function show(Student $student)
    {
        $student->load(['user', 'studyProgram.faculty']);

        return ApiResponse::success(
            (new StudentResource($student))->resolve(),
            'Detail mahasiswa berhasil diambil'
        );
    }

    public function update(UpsertStudentRequest $request, Student $student)
    {
        $student->update($request->validated());
        $student->load(['user', 'studyProgram.faculty']);

        return ApiResponse::success(
            (new StudentResource($student))->resolve(),
            'Mahasiswa berhasil diperbarui'
        );
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return ApiResponse::success(null, 'Mahasiswa berhasil dihapus');
    }
}
