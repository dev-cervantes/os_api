<?php

namespace App\Http\Controllers;

use App\Models\Os;
use App\Models\OsEquipamentoItem;
use App\Models\OsProduto;
use App\Models\OsServico;
use App\Models\OsSituacao;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OsController extends Controller
{
    const PAGINATOR_PER_PAGE_DEFAULT = 100;

    public function index(Request $request): JsonResponse
    {
        try {
            $body = $request->except(['per_page', 'page']);
            $queryParams = $request->only(['per_page', 'page']);

            $validate = $this->validator($body, $this->rulesFiltros(), $this->messagesFiltros());
            if ($validate->fails()) {
                throw new Exception($validate->errors()->first(), 422);
            }

            $filtros = $validate->getData();

            $query = Os::query();

            if (isset($filtros['data_inicial']))
                $query->where("data_hora", ">=", $filtros['data_inicial']);

            if (isset($filtros['data_final']))
                $query->where("data_hora", "<=", $filtros['data_final']);

            if (isset($filtros['codigo']))
                $query->where("os_codigo", "=", $filtros['codigo']);

            if (isset($filtros['situacao']))
                $query->where("id_os_situacao", "=", $filtros['situacao']);

            if (isset($filtros['cliente']))
                $query->whereHas("cliente", fn ($q) => $q->where("nome", "=", $filtros['cliente']));

            if (isset($filtros['equipamento']) || isset($filtros['equipamento_item'])) {
                $query->whereHas(
                    "equipamentosItens",
                    function ($equipamentosItens) use ($filtros) {
                        $equipamentosItens->whereHas("equipamentoItem", function ($equipamentoItem) use ($filtros) {
                            if (isset($filtros['equipamento_item']))
                                $equipamentoItem->where("id_equipamento_item", "=", $filtros['equipamento_item']);
//                                $equipamentoItem->where("identificador", "=", $filtros['equipamento_item']);

                            if (isset($filtros['equipamento']))
                                $equipamentoItem->whereHas("equipamento", fn ($equipamento) => $equipamento->where("id_equipamento", "=", $filtros['equipamento']));
//                                $equipamentoItem->whereHas("equipamento", fn ($equipamento) => $equipamento->where("descricao", "=", $filtros['equipamento']));
                        });
                    }
                );
            }

            $query->orderBy("os_codigo", "desc");

            $data = $query->paginate(@$queryParams['per_page'] ?? self::PAGINATOR_PER_PAGE_DEFAULT);

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
            unset($data['os_codigo']);

            $this->validar($data);

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

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $body = $request->all();

            $validate = $this->validator($body, $this->rules(), $this->messages());
            if ($validate->fails()) {
                throw new Exception($validate->errors()->first(), 422);
            }

            $data = $validate->getData();

            $this->validar($data);

            DB::beginTransaction();

            $os = Os::allRelations()->find($id);
            if (is_null($os)) {
                throw new Exception("OS não encontrada!");
            }

            $osEquipamentosItens = $os->equipamentosItens;

            $os->fill($data);
            $os->save();

            //Salva os novos equipamentos itens.
            foreach ($data['equipamentos_itens'] as $reqEquipamentoItem) {
                if (!isset($reqEquipamentoItem['id_os_equipamento_item'])) {
                    $osEquipamentoItem = OsEquipamentoItem::create($reqEquipamentoItem);

                    foreach ($reqEquipamentoItem['servicos'] as $servicos) {
                        $servicos['id_os_equipamento_item'] = $osEquipamentoItem->id_os_equipamento_item;
                        OsServico::create($servicos);
                    }

                    foreach ($reqEquipamentoItem['produtos'] as $produtos) {
                        $produtos['id_os_equipamento_item'] = $osEquipamentoItem->id_os_equipamento_item;
                        OsProduto::create($produtos);
                    }
                }
            }

            //Verifica os equipamentos itens para atualizar ou deletar.
            foreach ($osEquipamentosItens as $osEquipamentoItem) {
                $existe = false;

                //Percorre os equipamentos contidos na request.
                foreach ($data['equipamentos_itens'] as $reqEquipamentoItem) {
                    if ($osEquipamentoItem->id_os_equipamento_item == @$reqEquipamentoItem['id_os_equipamento_item']) {
                        $existe = true;

                        //Percorre os serviços verificando se tem algum novo para inserir no devido equipamento.
                        foreach ($reqEquipamentoItem['servicos'] as $reqServicos) {
                            if (!isset($reqServicos['id_os_servico'])) {
                                $reqServicos['id_os_equipamento_item'] = $osEquipamentoItem->id_os_equipamento_item;
                                OsServico::create($reqServicos);
                            }
                        }

                        //Percorre os serviços verificando se tem algum novo para inserir no devido equipamento.
                        foreach ($reqEquipamentoItem['produtos'] as $reqProdutos) {
                            if (!isset($reqProdutos['id_os_servico'])) {
                                $reqProdutos['id_os_equipamento_item'] = $osEquipamentoItem->id_os_equipamento_item;
                                OsServico::create($reqProdutos);
                            }
                        }

                        //Verifica os serviços para atualizar ou deletar.
                        foreach ($osEquipamentoItem->servicos as $osServico) {
                            $existeServico = false;

                            //Percorre os serviços contidos na request.
                            foreach ($reqEquipamentoItem['servicos'] as $reqServicos) {
                                if ($osServico->id_os_servico == @$reqEquipamentoItem['id_os_servico']) {
                                    $existeServico = true;

                                    $osServico->fill($reqServicos);
                                    $osServico->save();
                                }
                            }

                            if (!$existeServico) {
                                $osServico->delete();
                            }
                        }

                        //Verifica os produtos para atualizar ou deletar.
                        foreach ($osEquipamentoItem->produtos as $osProduto) {
                            $existeProduto = false;

                            //Percorre os produtos contidos na request.
                            foreach ($reqEquipamentoItem['produtos'] as $reqProduto) {
                                if ($osProduto->id_os_servico == @$reqEquipamentoItem['id_os_produto']) {
                                    $existeProduto = true;

                                    $osProduto->fill($reqProduto);
                                    $osProduto->save();
                                }
                            }

                            if (!$existeProduto) {
                                $osProduto->delete();
                            }
                        }

                        $osEquipamentoItem->fill($reqEquipamentoItem);
                        $osEquipamentoItem->save();
                    }
                }

                if (!$existe) {
                    $osEquipamentoItem->delete();
                }
            }

            DB::commit();

            $os = Os::allRelations()->find($id);

            return $this->sendResponse($os);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
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

            return $this->sendResponse($os);
        } catch (Exception $e) {
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    protected function validar($data)
    {
        if (empty($data['equipamentos_itens'])) {
            if (!isset($data['id_os_situacao']))
                throw new Exception("Situação não informada.");

            $situacao = OsSituacao::find($data);
            if (is_null($situacao))
                throw new Exception("Situação não encontrada.");

            if (!$situacao->aprovada)
                throw new Exception("OS deve possuir no mínio 1 serviço ou 1 produto.");
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

    protected function messages(): array
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
            "data_inicial" => "date_format:Y-m-d\TH:i:s.u|nullable",
            "data_final" => "date_format:Y-m-d\TH:i:s.u|nullable|after_or_equal:data_inicial",
            "codigo" => "integer|nullable",
            "situacao" => "string|nullable",
            "cliente" => "string|nullable",
            "equipamento" => "string|nullable",
            "equipamento_item" => "string|nullable"
        ];
    }

    protected function messagesFiltros(): array
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
