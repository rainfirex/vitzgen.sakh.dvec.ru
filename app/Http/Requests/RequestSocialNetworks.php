<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestSocialNetworks extends FormRequest
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
            'title_id' => 'required',
            'type'  => 'required',
            'url'   => 'required|unique:social_networks'
        ];
    }

    public function messages()
    {
        return [
            'title_id.required'   => 'Не заполнено обязательное поле "Наименование"',
            'type.required'    => 'Не заполнено обязательное поле "Социальная сеть"',
            'url.required' => 'Не заполнено обязательное поле "Адрес сети"',
            'url.unique' => 'Ссылка уже существует!'
        ];
    }
}
