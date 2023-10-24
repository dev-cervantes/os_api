<?php

namespace App\Http\Controllers;

use App\Http\Resources\OsTipoAtendimento\OsTipoAtendimentoCollectionResource;
use App\Models\OsTipoAtendimento;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class OsTipoAtendimentoController extends Controller
{
    public function index(): OsTipoAtendimentoCollectionResource
    {
        return new OsTipoAtendimentoCollectionResource(
            resource: Cache::remember(
                key: "osTipoAtendimento_index",
                ttl: 60 * 5, // 5 minutos,
                callback: fn() => OsTipoAtendimento::query()->orderBy("tipo_atendimento")->get()
            )
        );
    }
}
