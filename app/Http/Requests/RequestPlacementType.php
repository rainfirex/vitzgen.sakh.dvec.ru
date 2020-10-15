<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestPlacementType extends FormRequest
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
            'title' => 'required|unique:type_placements'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Не заполнено обязательное поле "Наименование"',
            'title.unique' => 'Такой тип уже существует!'
        ];
    }
}
