<?php
namespace App\Http\Requests\Orders;


class OrderSparePartDetailRequest extends OrderChildRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
            'id_printers_workplace' => 'required',
            'call_specialist' => 'boolean',
            'id_spare_part' => 'required_if:call_specialist,false',
            'files' => 'required_if:is_new,true|array|max:5',
            'files.*' => [
                'max:30720',
            ],
            'quantity' => 'required_if:call_specialist,false|integer|min:1',
        ];
    }

    public function attributes()
    {
        return [
            ...(array)config('labels.order'),
            ...(array)config('labels.order_spare_part'),
        ];
    }

    public function messages()
    {
        return [
            'files' => 'Необходимо загрузить ":attribute"',
            'quantity' => 'Количество должно быть не менее 1',
        ];
    }
}
