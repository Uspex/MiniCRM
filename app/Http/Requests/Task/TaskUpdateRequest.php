<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class TaskUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id'            => ['nullable', 'integer', 'exists:users,id'],
            'activity_id'        => ['required', 'integer', 'exists:activities,id'],
            'message'            => ['nullable', 'string', 'max:65535'],
            'product_count'      => ['nullable', 'integer', 'min:0'],
            'runtime'            => ['nullable', 'integer', 'min:0'],
            'status'             => ['boolean'],
        ];
    }
}
