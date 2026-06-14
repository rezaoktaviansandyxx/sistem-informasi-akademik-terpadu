<?php

namespace App\Http\Requests\Api\V1\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->route('student');
        $studentId = is_object($student) ? $student->getKey() : $student;

        return [
            'user_id' => ['required', 'uuid', 'exists:users,id'],
            'study_program_id' => ['required', 'uuid', 'exists:study_programs,id'],
            'nim' => [
                'required',
                'string',
                'max:20',
                Rule::unique('students', 'nim')->ignore($studentId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'academic_status' => ['required', 'in:active,inactive,leave,graduated,dropout'],
        ];
    }
}
