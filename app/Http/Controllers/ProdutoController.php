<?php

namespace App\Http\Controllers;

use App\Http\Resources\Produto\ProdutoCollectionResource;
use App\Models\Produto;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ProdutoController extends Controller
{
    public function index(): ProdutoCollectionResource
    {
        return new ProdutoCollectionResource(
            resource: Cache::remember(
                key: "produto_index",
                ttl: 60 * 5, // 5 minutos
                callback: fn() => Produto::query()->orderBy("descricao")->get()
            )
        );
    }
}
