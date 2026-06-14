<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MasterData\UpsertFacultyRequest;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Http\Resources\Api\V1\FacultyResource;
use App\Models\Faculty;
use App\Support\AppliesListSorting;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    use AppliesListSorting;

    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 15), 100);
        $search = (string) $request->query('search', '');
        $hasStudyPrograms = $request->query('has_study_programs');
        $sortBy = $request->query('sort_by');
        $sortDirection = $request->query('sort_direction');

        $query = Faculty::query()
            ->withCount('studyPrograms')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($hasStudyPrograms !== null, function ($query) use ($hasStudyPrograms): void {
                $hasItems = filter_var($hasStudyPrograms, FILTER_VALIDATE_BOOLEAN);

                if ($hasItems) {
                    $query->has('studyPrograms');
                } else {
                    $query->doesntHave('studyPrograms');
                }
            });

        $faculties = $this->applySorting(
            $query,
            is_string($sortBy) ? $sortBy : null,
            is_string($sortDirection) ? $sortDirection : null,
            ['name', 'study_programs_count', 'created_at'],
            'name'
        )
            ->paginate($perPage)
            ->withQueryString();

        return ApiResponse::success(
            [
                'items' => $faculties->getCollection()
                    ->map(fn (Faculty $faculty): array => (new FacultyResource($faculty))->resolve())
                    ->all(),
                'pagination' => [
                    'current_page' => $faculties->currentPage(),
                    'per_page' => $faculties->perPage(),
                    'total' => $faculties->total(),
                    'last_page' => $faculties->lastPage(),
                ],
            ],
            'Daftar fakultas berhasil diambil'
        );
    }

    public function store(UpsertFacultyRequest $request)
    {
        $faculty = Faculty::query()->create($request->validated());
        $faculty->loadCount('studyPrograms');

        return ApiResponse::success(
            (new FacultyResource($faculty))->resolve(),
            'Fakultas berhasil dibuat',
            null,
            201
        );
    }

    public function show(Faculty $faculty)
    {
        $faculty->loadCount('studyPrograms');

        return ApiResponse::success(
            (new FacultyResource($faculty))->resolve(),
            'Detail fakultas berhasil diambil'
        );
    }

    public function update(UpsertFacultyRequest $request, Faculty $faculty)
    {
        $faculty->update($request->validated());
        $faculty->loadCount('studyPrograms');

        return ApiResponse::success(
            (new FacultyResource($faculty))->resolve(),
            'Fakultas berhasil diperbarui'
        );
    }

    public function destroy(Faculty $faculty)
    {
        $faculty->delete();

        return ApiResponse::success(null, 'Fakultas berhasil dihapus');
    }
}
