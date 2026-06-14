<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ApiResponse;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {
    }

    public function __invoke(Request $request)
    {
        $role = $request->query('role', 'student');

        return ApiResponse::success(
            $this->dashboardService->summary($role),
            'Ringkasan dashboard berhasil diambil'
        );
    }
}
