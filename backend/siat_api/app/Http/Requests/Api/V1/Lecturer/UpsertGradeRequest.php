<?php

namespace App\Http\Requests\Api\V1\Lecturer;

use Illuminate\Foundation\Http\FormRequest;

class UpsertGradeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grades' => ['required', 'array', 'min:1'],
            'grades.*.student_id' => ['required', 'uuid'],
            'grades.*.assignment_score' => ['nullable', 'numeric', 'between:0,100'],
            'grades.*.mid_score' => ['nullable', 'numeric', 'between:0,100'],
            'grades.*.final_score' => ['nullable', 'numeric', 'between:0,100'],
        ];
    }
}
