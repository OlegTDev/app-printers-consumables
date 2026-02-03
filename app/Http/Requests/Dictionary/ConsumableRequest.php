<?php

namespace App\Http\Requests\Dictionary;

use App\Models\Consumable\Consumable;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\RequiredIf;

/**
 * Расходный материал
 * @property mixed $type
 * @property mixed $name
 */
class ConsumableRequest extends FormRequest
{
    /**
     * @var Consumable|null
     */
    private $_consumable;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->_consumable = Route::input('consumable');
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'required',
            'name' => [
                'required',
                'max:255',
                Rule::unique('consumables')->where(function (Builder $query) {
                    $query->where('type', $this?->type)
                            ->where('name', $this?->name)
                            ->where('id', '<>', $this?->_consumable?->id ?? null);
                }),
            ],
            'color' => new RequiredIf($this->type == 'cartridge'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function attributes()
    {
        return config('labels.consumable');
    }
}
