<?php

namespace App\Http\Requests\Api\V1\Governance;

use Illuminate\Foundation\Http\FormRequest;

class ApprovalDecisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:approved,rejected,needs_revision'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
