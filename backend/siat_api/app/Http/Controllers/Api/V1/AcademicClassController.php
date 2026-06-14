<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MasterData\UpsertAcademicClassRequest;
use App\Http\Resources\Api\V1\AcademicClassResource;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Models\AcademicClass;
use App\Support\AppliesListSorting;
use Illuminate\Http\Request;

class AcademicClassController extends Controller
{
    use AppliesListSorting;

    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 15), 100);
        $search = (string) $request->query('search', '');
        $courseId = (string) $request->query('course_id', '');
        $semesterId = (string) $request->query('semester_id', '');
        $sortBy = $request->query('sort_by');
        $sortDirection = $request->query('sort_direction');

        $query = AcademicClass::query()
            ->with(['course', 'semester.academicYear'])
            ->withCount('krsDetails')
            ->when($courseId !== '', function ($query) use ($courseId): void {
                $query->where('course_id', $courseId);
            })
            ->when($semesterId !== '', function ($query) use ($semesterId): void {
                $query->where('semester_id', $semesterId);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($classQuery) use ($search): void {
                    $classQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhereHas('course', function ($courseQuery) use ($search): void {
                            $courseQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            });

        $classes = $this->applySorting(
            $query,
            is_string($sortBy) ? $sortBy : null,
            is_string($sortDirection) ? $sortDirection : null,
            ['name', 'capacity', 'krs_details_count', 'created_at'],
            'name'
        )
            ->paginate($perPage)
            ->withQueryString();

        return ApiResponse::success(
            [
                'items' => $classes->getCollection()
                    ->map(fn (AcademicClass $academicClass): array => (new AcademicClassResource($academicClass))->resolve())
                    ->all(),
                'pagination' => [
                    'current_page' => $classes->currentPage(),
                    'per_page' => $classes->perPage(),
                    'total' => $classes->total(),
                    'last_page' => $classes->lastPage(),
                ],
            ],
            'Daftar kelas akademik berhasil diambil'
        );
    }

    public function store(UpsertAcademicClassRequest $request)
    {
        $academicClass = AcademicClass::query()->create($request->validated());
        $academicClass->load(['course', 'semester.academicYear'])->loadCount('krsDetails');

        return ApiResponse::success(
            (new AcademicClassResource($academicClass))->resolve(),
            'Kelas akademik berhasil dibuat',
            null,
            201
        );
    }

    public function show(AcademicClass $academicClass)
    {
        $academicClass->load(['course', 'semester.academicYear'])->loadCount('krsDetails');

        return ApiResponse::success(
            (new AcademicClassResource($academicClass))->resolve(),
            'Detail kelas akademik berhasil diambil'
        );
    }

    public function update(UpsertAcademicClassRequest $request, AcademicClass $academicClass)
    {
        $academicClass->update($request->validated());
        $academicClass->load(['course', 'semester.academicYear'])->loadCount('krsDetails');

        return ApiResponse::success(
            (new AcademicClassResource($academicClass))->resolve(),
            'Kelas akademik berhasil diperbarui'
        );
    }

    public function destroy(AcademicClass $academicClass)
    {
        $academicClass->delete();

        return ApiResponse::success(null, 'Kelas akademik berhasil dihapus');
    }
}
