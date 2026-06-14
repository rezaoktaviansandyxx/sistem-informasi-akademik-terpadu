<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MasterData\UpsertSemesterRequest;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Http\Resources\Api\V1\SemesterResource;
use App\Models\Semester;
use App\Support\AppliesListSorting;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    use AppliesListSorting;

    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 15), 100);
        $search = (string) $request->query('search', '');
        $academicYearId = (string) $request->query('academic_year_id', '');
        $active = $request->query('is_active');
        $sortBy = $request->query('sort_by');
        $sortDirection = $request->query('sort_direction');

        $query = Semester::query()
            ->with('academicYear')
            ->withCount('academicClasses')
            ->when($academicYearId !== '', function ($query) use ($academicYearId): void {
                $query->where('academic_year_id', $academicYearId);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($active !== null, function ($query) use ($active): void {
                $query->where('is_active', filter_var($active, FILTER_VALIDATE_BOOLEAN));
            });

        $semesters = $this->applySorting(
            $query,
            is_string($sortBy) ? $sortBy : null,
            is_string($sortDirection) ? $sortDirection : null,
            ['name', 'is_active', 'academic_classes_count', 'created_at'],
            'is_active',
            'desc'
        )
            ->paginate($perPage)
            ->withQueryString();

        return ApiResponse::success(
            [
                'items' => $semesters->getCollection()
                    ->map(fn (Semester $semester): array => (new SemesterResource($semester))->resolve())
                    ->all(),
                'pagination' => [
                    'current_page' => $semesters->currentPage(),
                    'per_page' => $semesters->perPage(),
                    'total' => $semesters->total(),
                    'last_page' => $semesters->lastPage(),
                ],
            ],
            'Daftar semester berhasil diambil'
        );
    }

    public function store(UpsertSemesterRequest $request)
    {
        $semester = Semester::query()->create($request->validated());
        $semester->load('academicYear')->loadCount('academicClasses');

        return ApiResponse::success(
            (new SemesterResource($semester))->resolve(),
            'Semester berhasil dibuat',
            null,
            201
        );
    }

    public function show(Semester $semester)
    {
        $semester->load('academicYear')->loadCount('academicClasses');

        return ApiResponse::success(
            (new SemesterResource($semester))->resolve(),
            'Detail semester berhasil diambil'
        );
    }

    public function update(UpsertSemesterRequest $request, Semester $semester)
    {
        $semester->update($request->validated());
        $semester->load('academicYear')->loadCount('academicClasses');

        return ApiResponse::success(
            (new SemesterResource($semester))->resolve(),
            'Semester berhasil diperbarui'
        );
    }

    public function destroy(Semester $semester)
    {
        $semester->delete();

        return ApiResponse::success(null, 'Semester berhasil dihapus');
    }
}
