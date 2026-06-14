<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SemesterResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_active' => (bool) $this->is_active,
            'academic_year' => $this->whenLoaded('academicYear', fn (): array => [
                'id' => $this->academicYear?->id,
                'label' => $this->academicYear?->label,
                'is_active' => (bool) $this->academicYear?->is_active,
            ]),
            'academic_classes_count' => $this->whenCounted('academicClasses'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
