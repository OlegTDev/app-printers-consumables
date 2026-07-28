<?php

namespace App\Http\Requests;

use App\Models\Consumable\ConsumableCount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class ConsumableCountInstalledRequest extends FormRequest
{

    private ?ConsumableCount $_consumableCount;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $this->_consumableCount = Route::input('count');
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
            'id_consumable_count' => 'required',
            'id_printer_workplace' => 'required',
            'count' => [
                'required',
                'integer',
                'min:1',
                'max:' . $this->getMaxCount(),
            ],
        ];
    }

    /**
     * Максимально возможное количество, которое можно вычесть
     */
    private function getMaxCount(): int
    {
        return $this->_consumableCount->count ?? 0;
    }

    public function attributes(): array
    {
        return config('labels.consumable_count_installed');
    }

}
