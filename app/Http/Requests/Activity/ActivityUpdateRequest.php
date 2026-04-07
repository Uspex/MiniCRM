<?php

namespace App\Http\Requests\Activity;

use Illuminate\Foundation\Http\FormRequest;

class ActivityUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => ['required', 'min:2', 'max:191'],
            'plan_quantity' => ['nullable', 'integer', 'min:0'],
            'plan_time'     => ['nullable', 'integer', 'min:0'],
        ];
    }
}
