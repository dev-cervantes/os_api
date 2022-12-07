<?php

namespace App\Http\Controllers;

use App\Models\OsTipoAtendimento;
use Exception;
use Illuminate\Http\JsonResponse;

class OsTipoAtendimentoController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $tiposAtendimento = OsTipoAtendimento::query()->orderBy("tipo_atendimento")->get();
            return $this->sendResponse($tiposAtendimento);
        } catch (Exception $e) {
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }
}
