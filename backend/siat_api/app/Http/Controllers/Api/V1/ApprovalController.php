<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Governance\ApprovalDecisionRequest;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Http\Resources\Api\V1\ApprovalResource;
use App\Services\Governance\ApprovalService;

class ApprovalController extends Controller
{
    public function __construct(
        private readonly ApprovalService $approvalService
    ) {
    }

    public function index()
    {
        return ApiResponse::success(
            $this->approvalService->list()
                ->map(fn ($approval): array => (new ApprovalResource($approval))->resolve())
                ->all(),
            'Daftar approval berhasil diambil'
        );
    }

    public function decide(ApprovalDecisionRequest $request, string $approvalId)
    {
        return ApiResponse::success(
            (new ApprovalResource(
                $this->approvalService->decide($approvalId, $request->validated())
            ))->resolve(),
            'Keputusan approval berhasil disimpan'
        );
    }
}
