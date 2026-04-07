<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {

    // We check if the authenticated user "can" update this specific report
    return $this->user()->can('update', $this->route('report'));

    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'min:10', 'max:255'],
            'severity' => ['sometimes', 'in:Low,Medium,High,Critical'],
            'description' => ['sometimes', 'string', 'min:20'],
        ];
    }
}
