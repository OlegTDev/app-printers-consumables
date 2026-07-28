<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
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
            'selectedRoles' => ['required', 'array'],
            'selectedOrganizations' => [
                Rule::requiredIf(function () {
                    $roles = request()->input('selectedRoles', []);

                    if (!\is_array($roles)) {
                        return false;
                    }

                    return !\in_array('admin', $roles, true);
                }),
                'array',
            ],
        ];
    }
}
