<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Пользователь
 * 
 * @property string $name
 * @property string $domain
 */
class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check() && Auth::user()->hasRole('admin');
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
            'domain' => ['required', 'max:50'],
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => 'Учетная запись',
            'domain' => 'Имя домена',
        ];
    }
}
