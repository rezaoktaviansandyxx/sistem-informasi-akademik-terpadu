<?php

namespace App\Http\Requests\Api\V1\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertLecturerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $lecturer = $this->route('lecturer');
        $lecturerId = is_object($lecturer) ? $lecturer->getKey() : $lecturer;

        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'nidn' => [
                'required',
                'string',
                'max:20',
                Rule::unique('lecturers', 'nidn')->ignore($lecturerId),
            ],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
