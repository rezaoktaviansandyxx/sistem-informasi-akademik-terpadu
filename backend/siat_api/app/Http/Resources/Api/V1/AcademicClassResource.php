<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicClassResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'capacity' => $this->capacity,
            'enrolled_count' => $this->whenCounted('krsDetails'),
            'course' => $this->whenLoaded('course', fn (): array => [
                'id' => $this->course?->id,
                'code' => $this->course?->code,
                'name' => $this->course?->name,
                'credits' => $this->course?->credits,
            ]),
            'semester' => $this->whenLoaded('semester', fn (): array => [
                'id' => $this->semester?->id,
                'name' => $this->semester?->name,
                'is_active' => $this->semester?->is_active,
                'academic_year' => $this->semester?->academicYear ? [
                    'id' => $this->semester->academicYear->id,
                    'label' => $this->semester->academicYear->label,
                    'is_active' => $this->semester->academicYear->is_active,
                ] : null,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
