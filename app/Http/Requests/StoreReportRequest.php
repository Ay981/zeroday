<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:10', 'max:255'],
            'severity' => ['required', 'in:Low,Medium,High,Critical'],
            'description' => ['required', 'string', 'min:20'],
            'program_id' => ['required', 'integer', 'exists:programs,id'],
        ];
    }
}
