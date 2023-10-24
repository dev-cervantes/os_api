<?php

namespace App\Http\Controllers;

use App\Http\Resources\Servico\ServicoCollectionResource;
use App\Models\Servico;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ServicoController extends Controller
{
    public function index(): ServicoCollectionResource
    {
        return new ServicoCollectionResource(
            resource: Cache::remember(
                key: "servico_index",
                ttl: 60 * 5, // 5 minutos,
                callback: fn() => Servico::query()->orderBy("descricao")->get()
            )
        );
    }
}
