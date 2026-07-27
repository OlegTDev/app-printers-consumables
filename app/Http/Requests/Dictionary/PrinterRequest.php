<?php

namespace App\Http\Requests\Dictionary;

use App\Models\Printer\Printer;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

/**
 * Принтер
 */
class PrinterRequest extends FormRequest
{

    private ?Printer $_printer;

    public function authorize()
    {
        $this->_printer = Route::input('printer', new Printer());
        return true;
    }

    public function rules()
    {
        return [
            'vendor' => [
                'required',
                'max:100',
                Rule::unique('printers')->where(function (Builder $query) {
                    $query->where('vendor', $this->vendor)
                            ->where('model', $this->model)
                            ->where('id', '<>', $this->_printer?->id);
                }),
            ],
            'model' => [
                'required',
                'max:200',
                Rule::unique('printers')->where(function (Builder $query) {
                    $query->where('vendor', $this->vendor)
                            ->where('model', $this->model)
                            ->where('id', '<>', $this->_printer?->id);
                }),
            ],
            'is_color_print' => 'required|boolean',
        ];
    }

    public function attributes(): array
    {
        return config('labels.printer');
    }

}
