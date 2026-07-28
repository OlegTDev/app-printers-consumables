<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


/**
 * Пользователь
 *
 * @property string $name
 * @property string $domain
 */
class UserCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'max:50', 'unique:users'],
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => 'Учетная запись',
        ];
    }
}
