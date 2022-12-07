<?php

namespace App\Http\Controllers;

use App\Models\Equipamento;
use App\Models\EquipamentoItem;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EquipamentoController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $equipamentos = Equipamento::with(["itens" => fn ($q) => $q->withoutGlobalScope(EquipamentoItem::scopeEquipamentoRelation)])
                ->orderBy("descricao")
                ->get();
            return $this->sendResponse($equipamentos);
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

            $data = $validate->getData();

            DB::beginTransaction();

            $equipamento = Equipamento::create($data);

            foreach ($data['itens'] as $item) {
                $item['id_equipamento'] = $equipamento->id_equipamento;
                EquipamentoItem::create($item);
            }

            DB::commit();

            $equipamento = Equipamento::with(["itens" => fn ($q) => $q->withoutGlobalScope(EquipamentoItem::scopeEquipamentoRelation)])->find($equipamento->id_equipamento);

            return $this->sendResponse($equipamento);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    protected function rules(): array
    {
        return [
            "descricao" => "required",
            "equipamento_codigo" => "nullable",
            "itens" => "array|required",
            "itens.*.identificador" => "required"
        ];
    }

    protected function messages(): array
    {
        return [
            "descricao.required" => "Descrição não informada.",
            "itens" => "Nenhum identificador informado para o equipamento.",
            "itens.*.identificador" => "Identificador não informado."
        ];
    }
}
