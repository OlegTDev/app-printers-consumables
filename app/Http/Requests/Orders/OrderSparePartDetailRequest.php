<?php
namespace App\Http\Requests\Orders;

use App\Models\Order\OrderSparePartDetails;

class OrderSparePartDetailRequest extends SubOrder
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
        ];
    }

    public function attributes()
    {
        return OrderSparePartDetails::labels();
    }
}