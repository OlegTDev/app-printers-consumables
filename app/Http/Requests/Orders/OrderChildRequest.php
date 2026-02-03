<?php
namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class OrderChildRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'comment' => 'string|nullable',
            'quantity' => 'required|integer|min:1',
        ];
    }
}
