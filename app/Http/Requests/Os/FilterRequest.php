<?php

namespace App\Http\Requests\Os;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "data_inicial" => "date_format:Y-m-d\TH:i:s.u|nullable",
            "data_final" => "date_format:Y-m-d\TH:i:s.u|nullable|after_or_equal:data_inicial",
            "codigo" => "integer|nullable",
            "situacao" => "string|nullable",
            "cliente" => "string|nullable",
            "responsavel" => "string|nullable",
            "equipamento" => "string|nullable",
            "equipamento_item" => "string|nullable"
        ];
    }
}
