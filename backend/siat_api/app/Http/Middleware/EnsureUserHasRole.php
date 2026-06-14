<?php

namespace App\Http\Middleware;

use App\Http\Resources\Api\V1\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return ApiResponse::error('Unauthenticated.', ['auth' => ['Pengguna belum login.']], 401);
        }

        $hasRole = $user->roles()
            ->whereIn('code', $roles)
            ->exists();

        if (! $hasRole) {
            return ApiResponse::error(
                'Akses ditolak untuk role ini.',
                ['role' => ['Role pengguna tidak memiliki izin untuk endpoint ini.']],
                403
            );
        }

        return $next($request);
    }
}
