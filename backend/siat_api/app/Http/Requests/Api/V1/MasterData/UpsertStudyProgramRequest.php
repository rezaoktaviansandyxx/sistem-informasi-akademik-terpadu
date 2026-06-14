<?php

namespace App\Http\Requests\Api\V1\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertStudyProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studyProgram = $this->route('studyProgram');
        $studyProgramId = is_object($studyProgram) ? $studyProgram->getKey() : $studyProgram;

        return [
            'faculty_id' => ['required', 'uuid', 'exists:faculties,id'],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('study_programs', 'code')->ignore($studyProgramId),
            ],
            'name' => ['required', 'string', 'max:255'],
        ];
    }
}
