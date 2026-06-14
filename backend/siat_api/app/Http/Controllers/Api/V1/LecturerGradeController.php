<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Lecturer\UpsertGradeRequest;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Services\Lecturer\LecturerGradeService;

class LecturerGradeController extends Controller
{
    public function __construct(
        private readonly LecturerGradeService $lecturerGradeService
    ) {
    }

    public function index(string $classId)
    {
        return ApiResponse::success(
            $this->lecturerGradeService->list($classId),
            'Daftar nilai kelas berhasil diambil'
        );
    }

    public function update(UpsertGradeRequest $request, string $classId)
    {
        return ApiResponse::success(
            $this->lecturerGradeService->upsert($classId, $request->validated()),
            'Draft nilai berhasil disimpan'
        );
    }

    public function finalize(string $classId)
    {
        return ApiResponse::success(
            $this->lecturerGradeService->finalize($classId),
            'Nilai berhasil difinalisasi'
        );
    }
}
