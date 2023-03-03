<?php

namespace App\Http\Controllers;

use App\Models\ConfigOs;
use App\Models\OsSituacao;
use Exception;
use Illuminate\Http\JsonResponse;

class OsSituacaoController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $config = ConfigOs::query()->first();
            $situacoes = OsSituacao::query()->orderBy("situacao")->get();

            $situacoes->each(fn ($it) => $it->encerrado = $it->id_os_situacao == $config->id_os_situacao_encerrada);

            return $this->sendResponse($situacoes);
        } catch (Exception $e) {
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }
}
