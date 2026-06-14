<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MasterData\UpsertLecturerRequest;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Http\Resources\Api\V1\LecturerResource;
use App\Models\Lecturer;
use App\Support\AppliesListSorting;
use Illuminate\Http\Request;

class LecturerController extends Controller
{
    use AppliesListSorting;

    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 15), 100);
        $search = (string) $request->query('search', '');
        $sortBy = $request->query('sort_by');
        $sortDirection = $request->query('sort_direction');

        $query = Lecturer::query()
            ->with('user')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($lecturerQuery) use ($search): void {
                    $lecturerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('nidn', 'like', "%{$search}%");
                });
            });

        $lecturers = $this->applySorting(
            $query,
            is_string($sortBy) ? $sortBy : null,
            is_string($sortDirection) ? $sortDirection : null,
            ['name', 'nidn', 'created_at'],
            'name'
        )
            ->paginate($perPage)
            ->withQueryString();

        return ApiResponse::success(
            [
                'items' => $lecturers->getCollection()
                    ->map(fn (Lecturer $lecturer): array => (new LecturerResource($lecturer))->resolve())
                    ->all(),
                'pagination' => [
                    'current_page' => $lecturers->currentPage(),
                    'per_page' => $lecturers->perPage(),
                    'total' => $lecturers->total(),
                    'last_page' => $lecturers->lastPage(),
                ],
            ],
            'Daftar dosen berhasil diambil'
        );
    }

    public function store(UpsertLecturerRequest $request)
    {
        $lecturer = Lecturer::query()->create($request->validated());
        $lecturer->load('user');

        return ApiResponse::success(
            (new LecturerResource($lecturer))->resolve(),
            'Dosen berhasil dibuat',
            null,
            201
        );
    }

    public function show(Lecturer $lecturer)
    {
        $lecturer->load('user');

        return ApiResponse::success(
            (new LecturerResource($lecturer))->resolve(),
            'Detail dosen berhasil diambil'
        );
    }

    public function update(UpsertLecturerRequest $request, Lecturer $lecturer)
    {
        $lecturer->update($request->validated());
        $lecturer->load('user');

        return ApiResponse::success(
            (new LecturerResource($lecturer))->resolve(),
            'Dosen berhasil diperbarui'
        );
    }

    public function destroy(Lecturer $lecturer)
    {
        $lecturer->delete();

        return ApiResponse::success(null, 'Dosen berhasil dihapus');
    }
}
