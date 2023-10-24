<?php

namespace App\Http\Controllers;

use App\Http\Resources\Usuario\UsuarioCollectionResource;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class UsuarioController extends Controller
{
    public function index(): UsuarioCollectionResource
    {
        return new UsuarioCollectionResource(
            resource: Cache::remember(
                key: "usuario_index",
                ttl: 60 * 5,
                callback: fn() => Usuario::query()->orderBy("nome")->get()
            )
        );
    }
}
