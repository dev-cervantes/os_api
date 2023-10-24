<?php

namespace App\Http\Controllers;

use App\Http\Resources\OsSituacao\SituacoesCollectionResource;
use App\Models\ConfigOs;
use App\Models\OsSituacao;
use Illuminate\Support\Facades\Cache;

class OsSituacaoController extends Controller
{
    public function index(): SituacoesCollectionResource
    {
        $situacoes = Cache::remember(
            key: "osSituacao_index",
            ttl: 60 * 5, // 5 minutos,
            callback: function () {
                $config = ConfigOs::query()->first();

                return OsSituacao::query()->orderBy("situacao")->get()
                    ->map(function ($it) use ($config) {
                        $it->encerrada = $it->id_os_situacao == $config->id_os_situacao_encerrada;
                        return $it;
                    });
            }
        );

        return new SituacoesCollectionResource(
            resource: $situacoes
        );
    }
}
