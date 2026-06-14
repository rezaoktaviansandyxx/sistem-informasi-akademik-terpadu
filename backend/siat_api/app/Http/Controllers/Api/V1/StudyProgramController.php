<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MasterData\UpsertStudyProgramRequest;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Http\Resources\Api\V1\StudyProgramResource;
use App\Models\StudyProgram;
use App\Support\AppliesListSorting;
use Illuminate\Http\Request;

class StudyProgramController extends Controller
{
    use AppliesListSorting;

    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 15), 100);
        $search = (string) $request->query('search', '');
        $facultyId = (string) $request->query('faculty_id', '');
        $sortBy = $request->query('sort_by');
        $sortDirection = $request->query('sort_direction');

        $query = StudyProgram::query()
            ->with('faculty')
            ->withCount(['courses', 'students'])
            ->when($facultyId !== '', function ($query) use ($facultyId): void {
                $query->where('faculty_id', $facultyId);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($studyProgramQuery) use ($search): void {
                    $studyProgramQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            });

        $studyPrograms = $this->applySorting(
            $query,
            is_string($sortBy) ? $sortBy : null,
            is_string($sortDirection) ? $sortDirection : null,
            ['name', 'code', 'courses_count', 'students_count', 'created_at'],
            'name'
        )
            ->paginate($perPage)
            ->withQueryString();

        return ApiResponse::success(
            [
                'items' => $studyPrograms->getCollection()
                    ->map(fn (StudyProgram $studyProgram): array => (new StudyProgramResource($studyProgram))->resolve())
                    ->all(),
                'pagination' => [
                    'current_page' => $studyPrograms->currentPage(),
                    'per_page' => $studyPrograms->perPage(),
                    'total' => $studyPrograms->total(),
                    'last_page' => $studyPrograms->lastPage(),
                ],
            ],
            'Daftar program studi berhasil diambil'
        );
    }

    public function store(UpsertStudyProgramRequest $request)
    {
        $studyProgram = StudyProgram::query()->create($request->validated());
        $studyProgram->load('faculty')->loadCount(['courses', 'students']);

        return ApiResponse::success(
            (new StudyProgramResource($studyProgram))->resolve(),
            'Program studi berhasil dibuat',
            null,
            201
        );
    }

    public function show(StudyProgram $studyProgram)
    {
        $studyProgram->load('faculty')->loadCount(['courses', 'students']);

        return ApiResponse::success(
            (new StudyProgramResource($studyProgram))->resolve(),
            'Detail program studi berhasil diambil'
        );
    }

    public function update(UpsertStudyProgramRequest $request, StudyProgram $studyProgram)
    {
        $studyProgram->update($request->validated());
        $studyProgram->load('faculty')->loadCount(['courses', 'students']);

        return ApiResponse::success(
            (new StudyProgramResource($studyProgram))->resolve(),
            'Program studi berhasil diperbarui'
        );
    }

    public function destroy(StudyProgram $studyProgram)
    {
        $studyProgram->delete();

        return ApiResponse::success(null, 'Program studi berhasil dihapus');
    }
}
