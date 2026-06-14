<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MasterData\UpsertCourseRequest;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Http\Resources\Api\V1\CourseResource;
use App\Models\Course;
use App\Support\AppliesListSorting;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    use AppliesListSorting;

    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 15), 100);
        $search = (string) $request->query('search', '');
        $studyProgramId = (string) $request->query('study_program_id', '');
        $credits = $request->query('credits');
        $sortBy = $request->query('sort_by');
        $sortDirection = $request->query('sort_direction');

        $query = Course::query()
            ->with(['studyProgram.faculty'])
            ->when($studyProgramId !== '', function ($query) use ($studyProgramId): void {
                $query->where('study_program_id', $studyProgramId);
            })
            ->when($credits !== null && is_numeric($credits), function ($query) use ($credits): void {
                $query->where('credits', (int) $credits);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($courseQuery) use ($search): void {
                    $courseQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            });

        $courses = $this->applySorting(
            $query,
            is_string($sortBy) ? $sortBy : null,
            is_string($sortDirection) ? $sortDirection : null,
            ['code', 'name', 'credits', 'created_at'],
            'code'
        )
            ->paginate($perPage)
            ->withQueryString();

        return ApiResponse::success(
            [
                'items' => $courses->getCollection()
                    ->map(fn (Course $course): array => (new CourseResource($course))->resolve())
                    ->all(),
                'pagination' => [
                    'current_page' => $courses->currentPage(),
                    'per_page' => $courses->perPage(),
                    'total' => $courses->total(),
                    'last_page' => $courses->lastPage(),
                ],
            ],
            'Daftar mata kuliah berhasil diambil'
        );
    }

    public function store(UpsertCourseRequest $request)
    {
        $course = Course::query()->create($request->validated());
        $course->load(['studyProgram.faculty']);

        return ApiResponse::success(
            (new CourseResource($course))->resolve(),
            'Mata kuliah berhasil dibuat',
            null,
            201
        );
    }

    public function show(Course $course)
    {
        $course->load(['studyProgram.faculty']);

        return ApiResponse::success(
            (new CourseResource($course))->resolve(),
            'Detail mata kuliah berhasil diambil'
        );
    }

    public function update(UpsertCourseRequest $request, Course $course)
    {
        $course->update($request->validated());
        $course->load(['studyProgram.faculty']);

        return ApiResponse::success(
            (new CourseResource($course))->resolve(),
            'Mata kuliah berhasil diperbarui'
        );
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return ApiResponse::success(null, 'Mata kuliah berhasil dihapus');
    }
}
