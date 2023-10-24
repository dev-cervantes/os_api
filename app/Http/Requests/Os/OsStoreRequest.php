<?php

namespace App\Http\Requests\Os;

use Illuminate\Foundation\Http\FormRequest;

class OsStoreRequest extends FormRequest
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
            "id_os_situacao" => "integer|required",
            "id_os_tipo_atendimento" => "integer|required",
            "id_cliente" => "integer|required",
            "id_usuario_atendente" => "integer|required",
            "id_usuario_aprovacao" => "integer|nullable",
            "id_usuario_encerramento" => "integer|nullable",
            "os_codigo" => "integer|nullable",
            "data_hora" => "date_format:Y-m-d H:i:s|required",
            "data_hora_previsao_entrega" => "date_format:Y-m-d H:i:s|nullable",
            "data_hora_entrega" => "date_format:Y-m-d H:i:s|nullable",
            "data_hora_aprovacao" => "date_format:Y-m-d H:i:s|nullable",
            "data_hora_encerramento" => "date_format:Y-m-d H:i:s|nullable",
            "obs" => "string|nullable",
            "inativo" => "boolean",
            "equipamentos_itens" => "array",
            "equipamentos_itens.*.id_equipamento_item" => "integer|required",
            "equipamentos_itens.*.problema_reclamado" => "string|nullable",
            "equipamentos_itens.*.problema_constatado" => "string|nullable",
            "equipamentos_itens.*.obs" => "string|nullable",
            "equipamentos_itens.*.servicos" => "array",
            "equipamentos_itens.*.produtos" => "array",
            "equipamentos_itens.*.servicos.*.id_servico" => "integer|required",
            "equipamentos_itens.*.servicos.*.qtd" => "numeric|required",
            "equipamentos_itens.*.servicos.*.id_usuario" => "integer|required",
            "equipamentos_itens.*.produtos.*.id_produto" => "integer|required",
            "equipamentos_itens.*.produtos.*.qtd" => "numeric|required",
        ];
    }

    public function messages(): array
    {
        return [
            "id_os_situacao.integer" => "Situação inválida.",
            "id_os_situacao" => "Situação não informada.",
            "id_os_tipo_atendimento.integer" => "Tipo de atendimento inválido.",
            "id_os_tipo_atendimento.required" => "Tipo de atendimento não informado.",
            "id_cliente.required" => "Cliente não informado.",
            "id_cliente.integer" => "Cliente inválido.",
            "id_usuario_atendente.integer" => "Usuário atendente inválido.",
            "id_usuario_atendente.required" => "Usuário atendente não informado.",
            "id_usuario_aprovacao.integer" => "Usuário aprovação inválido.",
            "id_usuario_encerramento.integer" => "Usuário encerramento inválido.",
            "os_codigo.integer" => "Código da OS é inválida.",
            "data_hora.date_format" => "Data inválida.",
            "data_hora.required" => "Data não informada.",
            "data_hora_previsao_entrega" => "Data inválida.",
            "data_hora_entrega" => "Data inválida.",
            "data_hora_aprovacao" => "Data inválida.",
            "data_hora_encerramento" => "Data inválida.",
            "obs" => "Observação inválida.",
            "equipamentos_itens.*.id_equipamento_item.integer" => "Identificador inválido.",
            "equipamentos_itens.*.id_equipamento_item.required" => "Identificador não informado.",
            "equipamentos_itens.*.servicos.*.id_servico.required" => "Serviço não informado.",
            "equipamentos_itens.*.servicos.*.qtd.required" => "Quantidade não informada para o serviço.",
            "equipamentos_itens.*.servicos.*.id_usuario.required" => "Usuário não informado para o serviço.",
            "equipamentos_itens.*.produtos.*.id_produto.required" => "Produto não informado.",
            "equipamentos_itens.*.produtos.*.qtd.required" => "Quantidade não informada para o produto.",
        ];
    }
}
