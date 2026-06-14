<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MasterData\UpsertAcademicYearRequest;
use App\Http\Resources\Api\V1\AcademicYearResource;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Models\AcademicYear;
use App\Support\AppliesListSorting;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    use AppliesListSorting;

    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 15), 100);
        $search = (string) $request->query('search', '');
        $active = $request->query('is_active');
        $sortBy = $request->query('sort_by');
        $sortDirection = $request->query('sort_direction');

        $query = AcademicYear::query()
            ->withCount('semesters')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where('label', 'like', "%{$search}%");
            })
            ->when($active !== null, function ($query) use ($active): void {
                $query->where('is_active', filter_var($active, FILTER_VALIDATE_BOOLEAN));
            });

        $academicYears = $this->applySorting(
            $query,
            is_string($sortBy) ? $sortBy : null,
            is_string($sortDirection) ? $sortDirection : null,
            ['label', 'is_active', 'semesters_count', 'created_at'],
            'label',
            'desc'
        )
            ->paginate($perPage)
            ->withQueryString();

        return ApiResponse::success(
            [
                'items' => $academicYears->getCollection()
                    ->map(fn (AcademicYear $academicYear): array => (new AcademicYearResource($academicYear))->resolve())
                    ->all(),
                'pagination' => [
                    'current_page' => $academicYears->currentPage(),
                    'per_page' => $academicYears->perPage(),
                    'total' => $academicYears->total(),
                    'last_page' => $academicYears->lastPage(),
                ],
            ],
            'Daftar tahun akademik berhasil diambil'
        );
    }

    public function store(UpsertAcademicYearRequest $request)
    {
        $academicYear = AcademicYear::query()->create($request->validated());
        $academicYear->loadCount('semesters');

        return ApiResponse::success(
            (new AcademicYearResource($academicYear))->resolve(),
            'Tahun akademik berhasil dibuat',
            null,
            201
        );
    }

    public function show(AcademicYear $academicYear)
    {
        $academicYear->loadCount('semesters');

        return ApiResponse::success(
            (new AcademicYearResource($academicYear))->resolve(),
            'Detail tahun akademik berhasil diambil'
        );
    }

    public function update(UpsertAcademicYearRequest $request, AcademicYear $academicYear)
    {
        $academicYear->update($request->validated());
        $academicYear->loadCount('semesters');

        return ApiResponse::success(
            (new AcademicYearResource($academicYear))->resolve(),
            'Tahun akademik berhasil diperbarui'
        );
    }

    public function destroy(AcademicYear $academicYear)
    {
        $academicYear->delete();

        return ApiResponse::success(null, 'Tahun akademik berhasil dihapus');
    }
}
