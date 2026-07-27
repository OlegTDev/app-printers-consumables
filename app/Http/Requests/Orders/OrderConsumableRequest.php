<?php

namespace App\Http\Requests\Orders;

class OrderConsumableRequest extends OrderChildRequest
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
            'id_consumable' => 'required',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function attributes()
    {
        return [
            ...(array)config('labels.order'),
            ...(array)config('labels.order_consumable'),
        ];
    }
}
