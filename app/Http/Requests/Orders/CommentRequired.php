<?php
namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequired extends FormRequest
{
    public function rules(): array
    {        
        return [            
            'comment' => 'required',
        ];
    }

    public function attributes()
    {
        return ['comment' => config('labels.order.comment')];
    }

}