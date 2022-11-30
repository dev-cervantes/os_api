<?php

namespace App\Http\Controllers;

use App\Models\Os;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $body = $request->all();

            $validate = $this->validator($body, $this->rules(), $this->messages());
            if ($validate->fails()) {
                throw new Exception($validate->errors()->first(), 422);
            }

            $filtros = $validate->getData();

            $query = Os::query();

            if (isset($filtros['codigo']))
                $query->where("os_codigo", "=", $filtros['codigo']);

            $query->whereBetween("data_hora", [$filtros['data_inicial'] ?? "0001-01-01", $filtros['data_final'] ?? "5000-12-30"]);

            if (isset($filtros['situacao']))
                $query->whereHas("situacao", fn ($q) => $q->where("situacao", "=", $filtros['situacao']));

            if (isset($filtros['cliente']))
                $query->whereHas("cliente", fn ($q) => $q->where("nome", "=", $filtros['cliente']));

            if (isset($filtros['equipamento']) || isset($filtros['equipamento_item']))
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

            $data = $query->get();

            return $this->sendResponse($data);
        } catch (Exception $e) {
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        //
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

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        //
    }

    protected function rules(): array
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

    protected function messages()
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
