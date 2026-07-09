<?php

namespace App\Http\Requests;

use App\Models\Consumable\ConsumableCount;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Количество расходных материалов
 */
class ConsumableCountRequest extends FormRequest
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
        $rules = [
            'count' => [
                'required',
                'integer',
                'min:1',
            ],
            'selectedOrganizations' => 'array',
        ];
        if ($this->isMethod('post')) {
            $rules['selectedOrganizations'] = 'required';
            $rules['id_consumable'] = 'required';
        };
        return $rules;
    }

    /**
     * {@inheritDoc}
     */
    public function attributes()
    {
        return ConsumableCount::labels();
    }
}
