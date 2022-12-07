<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use Exception;
use Illuminate\Http\JsonResponse;

class ServicoController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $servicos = Servico::query()->orderBy("descricao")->get();
            return $this->sendResponse($servicos);
        } catch (Exception $e) {
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }
}
