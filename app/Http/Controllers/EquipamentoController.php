<?php

namespace App\Http\Controllers;

use App\Http\Requests\Equipamento\EquipamentoStoreRequest;
use App\Http\Resources\Equipamento\EquipamentoCollectionResource;
use App\Http\Resources\Equipamento\EquipamentoResource;
use App\Models\Equipamento;
use App\Models\EquipamentoItem;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EquipamentoController extends Controller
{
    public function index(): EquipamentoCollectionResource
    {
        $equipamentos = Cache::remember(
            key: "equipamento_index",
            ttl: 60 * 2, // 2 minutos
            callback: function () {
                Equipamento::with(["itens" => fn($q) => $q->withoutGlobalScope(EquipamentoItem::scopeEquipamentoRelation)])
                    ->orderBy("descricao")
                    ->get();
            });

        return new EquipamentoCollectionResource(
            resource: $equipamentos
        );
    }

    public function store(EquipamentoStoreRequest $request): EquipamentoResource
    {
        DB::beginTransaction();

        $equipamento = Equipamento::create($request->all());

        foreach ($request->get('itens') as $item) {
            $item['id_equipamento'] = $equipamento->id_equipamento;
            EquipamentoItem::create($item);
        }

        DB::commit();

        Cache::forget("equipamento_index");

        return new EquipamentoResource(resource: $equipamento);
    }
}
