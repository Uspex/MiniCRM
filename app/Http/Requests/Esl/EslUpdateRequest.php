<?php

namespace App\Http\Requests\Esl;

use Illuminate\Foundation\Http\FormRequest;

class EslUpdateRequest extends FormRequest
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
            'shop_id' => ['required', 'exists:shops,id'],
            'tag_code' => ['required', 'min:2', 'max:191', 'unique:esls,tag_code,'. $this->route('esl')],
            'model' => ['required'],
            'showcase_id' => ['nullable', 'exists:showcases,id'],
            'template_id' => ['nullable', 'exists:templates,id'],
            'product_id' => ['nullable', 'exists:products,id'],
        ];
    }
}
