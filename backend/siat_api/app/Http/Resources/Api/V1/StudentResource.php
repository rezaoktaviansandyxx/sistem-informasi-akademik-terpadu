<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nim' => $this->nim,
            'name' => $this->name,
            'academic_status' => $this->academic_status,
            'user' => $this->whenLoaded('user', fn (): array => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ]),
            'study_program' => $this->whenLoaded('studyProgram', fn (): array => [
                'id' => $this->studyProgram?->id,
                'code' => $this->studyProgram?->code,
                'name' => $this->studyProgram?->name,
                'faculty' => $this->studyProgram?->faculty ? [
                    'id' => $this->studyProgram->faculty->id,
                    'name' => $this->studyProgram->faculty->name,
                ] : null,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
