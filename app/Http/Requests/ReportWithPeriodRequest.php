<?php

namespace App\Http\Requests;

class ReportWithPeriodRequest extends ReportBaseRequest
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
            ...parent::rules(),
            'withoutPeriod' => 'required',
            'dateFrom' => 'required_if:withoutPeriod,false',
            'dateTo' => 'required_if:withoutPeriod,false',
        ];
    }

    public function messages(): array
    {
        return [
            ...parent::messages(),
            'required_if' => 'Поле ":attribute" является обязательным для заполнения, если не выбрано поле ":other".',
        ];
    }

    public function attributes(): array
    {
       return config('labels.report');
    }
}
