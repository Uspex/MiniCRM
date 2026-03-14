<?php

namespace App\Http\Requests\Activity;

use Illuminate\Foundation\Http\FormRequest;

class ActivityCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'min:2', 'max:191'],
            'slug' => ['required', 'min:2', 'max:191', 'unique:activities'],
        ];
    }
}
