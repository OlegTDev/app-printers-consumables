<?php
namespace App\Http\Requests\Orders;

use App\Models\Order\OrderSparePartDetails;

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
            'files' => 'required|array|max:5',
            'files.*' => [
                'max:30720',
            ],
        ];
    }

    public function attributes()
    {
        return OrderSparePartDetails::labels();
    }
}