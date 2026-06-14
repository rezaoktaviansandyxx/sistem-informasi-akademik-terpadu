<?php

namespace App\Http\Requests\Api\V1\Academic;

use Illuminate\Foundation\Http\FormRequest;

class StoreKrsEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['nullable', 'uuid'],
            'class_id' => ['required', 'uuid'],
            'semester_id' => ['nullable', 'uuid'],
        ];
    }
}
