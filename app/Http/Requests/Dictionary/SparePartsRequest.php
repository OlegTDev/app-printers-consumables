<?php

namespace App\Http\Requests\Dictionary;

use App\Models\SpareParts;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

/**
 * @property mixed $name
 * @property mixed $description
 */
class SparePartsRequest extends FormRequest
{
    /**
     * @var SpareParts|null
     */
    private $_sparePart;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->_sparePart = Route::input('spare_part');
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
            'name' => [
                'required',
                'max:250',
                Rule::unique('spare_parts')->ignore($this->_sparePart),
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function attributes()
    {
        return SpareParts::labels();
    }
}
