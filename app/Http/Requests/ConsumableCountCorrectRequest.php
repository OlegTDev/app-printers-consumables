<?php

namespace App\Http\Requests;

use App\Models\Consumable\ConsumableCount;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Количество расходных материалов
 */
class ConsumableCountCorrectRequest extends FormRequest
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
            'id_consumable' => 'required',
            'count' => [
                'required',
                'integer',
                'min:0',
            ],
        ];        
    }

    /**
     * {@inheritDoc}
     */
    public function attributes()
    {
        return ConsumableCount::labels();
    }
}
