<?php

namespace App\Http\Controllers;

use App\Models\OsSituacao;
use Exception;
use Illuminate\Http\JsonResponse;

class OsSituacaoController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $situacoes = OsSituacao::query()->orderBy("situacao")->get();
            return $this->sendResponse($situacoes);
        } catch (Exception $e) {
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }
}
