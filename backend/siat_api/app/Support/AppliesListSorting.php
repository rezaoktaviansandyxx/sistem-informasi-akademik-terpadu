<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

trait AppliesListSorting
{
    protected function applySorting(
        Builder $query,
        ?string $sortBy,
        ?string $sortDirection,
        array $allowedSorts,
        string $defaultSortBy,
        string $defaultDirection = 'asc'
    ): Builder {
        $resolvedSortBy = in_array($sortBy, $allowedSorts, true)
            ? $sortBy
            : $defaultSortBy;

        $normalizedDefaultDirection = strtolower($defaultDirection) === 'desc'
            ? 'desc'
            : 'asc';

        $resolvedSortDirection = in_array(strtolower((string) $sortDirection), ['asc', 'desc'], true)
            ? strtolower((string) $sortDirection)
            : $normalizedDefaultDirection;

        return $query->orderBy($resolvedSortBy, $resolvedSortDirection);
    }
}
