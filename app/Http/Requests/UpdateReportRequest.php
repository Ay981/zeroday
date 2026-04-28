<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {

        return $this->user()->can('update', $this->route('report'));

    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'min:10', 'max:255'],
            'severity' => ['sometimes', 'in:Low,Medium,High,Critical'],
            'description' => ['sometimes', 'string', 'min:20'],
            'status' => ['sometimes', 'in:Open,In Progress,Patched'],
            'evidence_image' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'remove_image' => ['sometimes', 'in:true,false,1,0'],
        ];
    }
}
