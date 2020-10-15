<?php namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestSearch extends FormRequest
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
            'text' => 'required',
            'column' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'text.required' => 'Не задан текст запроса.',
            'column.required' => 'Не задан тип запроса.'
        ];
    }
}
