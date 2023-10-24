<?php

namespace App\Http\Requests\Equipamento;

use Illuminate\Foundation\Http\FormRequest;

class EquipamentoStoreRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            "descricao" => ["required"],
            "equipamento_codigo" => ["nullable"],
            "itens" => ["array", "required"],
            "itens.*.identificador" => ["required"]
        ];
    }

    public function messages(): array
    {
        return [
            "descricao.required" => "Descrição do equipamento não informado.",
            "itens" => "Nenhum identificador informado para o equipamento.",
            "itens.*.identificador" => "Identificador não informado."
        ];
    }
}
