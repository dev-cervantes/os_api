<?php

namespace App\Http\Controllers;

use App\Models\Os;
use App\Models\OsEquipamentoItem;
use App\Models\OsProduto;
use App\Models\OsServico;
use App\Models\OsUsuarioResponsavel;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $body = $request->all();

            $validate = $this->validator($body, $this->rulesFiltros(), $this->messagesFiltros());
            if ($validate->fails()) {
                throw new Exception($validate->errors()->first(), 422);
            }

            $filtros = $validate->getData();

            $query = Os::query();
            $query->whereBetween("data_hora", [$filtros['data_inicial'] ?? "0001-01-01", $filtros['data_final'] ?? "5000-12-30"]);

            if (isset($filtros['codigo']))
                $query->where("os_codigo", "=", $filtros['codigo']);

            if (isset($filtros['situacao']))
                $query->whereHas("situacao", fn ($q) => $q->where("situacao", "=", $filtros['situacao']));

            if (isset($filtros['cliente']))
                $query->whereHas("cliente", fn ($q) => $q->where("nome", "=", $filtros['cliente']));

            if (isset($filtros['equipamento']) || isset($filtros['equipamento_item'])) {
                $query->whereHas(
                    "equipamentosItens",
                    function ($equipamentosItens) use ($filtros) {
                        $equipamentosItens->whereHas("equipamentoItem", function ($equipamentoItem) use ($filtros) {
                            if (isset($filtros['equipamento_item']))
                                $equipamentoItem->where("identificador", "=", $filtros['equipamento_item']);

                            if (isset($filtros['equipamento']))
                                $equipamentoItem->whereHas("equipamento", fn ($equipamento) => $equipamento->where("descricao", "=", $filtros['equipamento']));
                        });
                    }
                );
            }

            $data = $query->get();

            return $this->sendResponse($data);
        } catch (Exception $e) {
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $body = $request->all();

            $validate = $this->validator($body, $this->rules(), $this->messages());
            if ($validate->fails()) {
                throw new Exception($validate->errors()->first(), 422);
            }

            DB::beginTransaction();

            $data = $validate->getData();

            $os = Os::create($data);

            foreach ($data['equipamentos_itens'] as $equipamentoItens) {
                $equipamentoItens['id_os'] = $os->id_os;
                $osEquipamentoItens = OsEquipamentoItem::create($equipamentoItens);

                foreach ($equipamentoItens['servicos'] as $servicos) {
                    $servicos['id_os_equipamento_item'] = $osEquipamentoItens->id_os_equipamento_item;
                    OsServico::create($servicos);
                }

                foreach ($equipamentoItens['produtos'] as $produtos) {
                    $produtos['id_os_equipamento_item'] = $osEquipamentoItens->id_os_equipamento_item;
                    OsProduto::create($produtos);
                }
            }

            DB::commit();

            $response = Os::allRelations()->find($os->id_os);

            return $this->sendResponse($response);
        } catch (Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $os = Os::allRelations()->find($id);

            if (is_null($os))
                return $this->sendResponseError("OS não encontrada.");

            return $this->sendResponse($os);
        } catch (Exception $e) {
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        //
    }

    public function destroy(int $id): JsonResponse
    {
        $os = Os::find($id);
        if (is_null($os))
            return $this->sendResponseError("Os não encontrada!");

        $os->delete();

        return $this->sendResponse([]);
    }

    public function getByCodigoOs(int $codigo): JsonResponse
    {
        try {
            $os = Os::query()->allRelations()->where("os_codigo", "=", "$codigo")->first();

            if (is_null($os))
                return $this->sendResponseError("OS não encontrada.");

            $os->responsavel = OsUsuarioResponsavel::getResponsavel($os->id_os);

            return $this->sendResponse($os);
        } catch (Exception $e) {
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    protected function rules(): array
    {
        return [
            "id_os_situacao" => "integer|required",
            "id_os_tipo_atendimento" => "integer|required",
            "id_cliente" => "integer|required",
            "id_usuario_atendente" => "integer|required",
            "id_usuario_aprovacao" => "integer|required",
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

    protected function messages()
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
            "id_usuario_aprovacao.required" => "Usuário aprovação não informado.",
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

    protected function rulesFiltros(): array
    {
        return [
            "data_inicial" => "date_format:Y-m-d|nullable",
            "data_final" => "date_format:Y-m-d|nullable|after_or_equal:data_inicial",
            "codigo" => "integer|nullable",
            "situacao" => "string|nullable",
            "cliente" => "string|nullable",
            "equipamento" => "string|nullable",
            "equipamento_item" => "string|nullable"
        ];
    }

    protected function messagesFiltros()
    {
        return [
            "date_format" => "Data é inválida.",
            "after_or_equal" => "Data final deve ser maior que a data inicial",
            "codigo.string" => "Código da OS é inválido.",
            "situacao.string" => "Situação da OS é inválida.",
            "cliente.string" => "Cliente inválido.",
            "equipamento.string" => "Equipamento inválido.",
            "equipamento_item.string" => "Identificador inválido."
        ];
    }
}
