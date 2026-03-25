<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'     => ['required', 'min:2', 'max:191'],
            'username' => ['nullable', 'string', 'min:2', 'max:191', 'unique:users'],
            'email'    => ['nullable', 'string', 'email', 'max:191', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'max:20'],
        ];
    }
}
