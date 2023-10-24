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
use Illuminate\Support\Facades\DB;

class EquipamentoController extends Controller
{
    public function index(): EquipamentoCollectionResource
    {
        return new EquipamentoCollectionResource(
            resource: Equipamento::with(["itens" => fn($q) => $q->withoutGlobalScope(EquipamentoItem::scopeEquipamentoRelation)])
                ->orderBy("descricao")
                ->get()
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

        return new EquipamentoResource(resource: $equipamento);
    }
}
