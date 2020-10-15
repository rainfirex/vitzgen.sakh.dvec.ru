<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestPlacementCreate extends FormRequest
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
            'title'   => 'required|unique:placements',
            'type_id' => 'required',
            'address' => 'required|unique:placements'
        ];
    }

    public function messages()
    {
        return [
            'title.required'   => 'Не заполнено обязательное поле "Наименование"',
            'title.unique'     => 'Такое наименование уже существует!',
            'type_id.required'    => 'Не заполнено обязательное поле "Тип"',
            'address.required' => 'Не заполнено обязательное поле "Адрес"'
        ];
    }
}
