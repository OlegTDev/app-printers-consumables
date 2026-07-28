<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportBaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'selectedOrganizations' => 'required|array',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Поле ":attribute" является обязательным для заполнения.',
        ];
    }

    public function attributes(): array
    {
       return config('labels.report');
    }
}
