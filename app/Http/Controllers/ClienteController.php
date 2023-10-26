<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cliente\ContainsNameRequest;
use App\Http\Resources\Cliente\ClienteCollectionResource;
use App\Http\Resources\Cliente\ClienteResource;
use App\Models\Cliente;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ClienteController extends Controller
{
    public function show(int $id): ClienteResource
    {
        $cliente = Cache::remember(
            key: "cliente_show_$id",
            ttl: 60 * 2, // 2 minutos,
            callback: function () use ($id) {
                return Cliente::query()->find($id);
            }) ?? throw new BadRequestException("Cliente não encontrado.", 404);

        return new ClienteResource(
            resource: $cliente
        );
    }

    public function containsName(ContainsNameRequest $request): ClienteCollectionResource
    {
        if (!$request->has('name')) {
            return new ClienteCollectionResource([]);
        }

        return new ClienteCollectionResource(
            resource: Cliente::query()
                ->where('nome', 'ilike', "%{$request->get('name')}%")
                ->orderBy('nome')
                ->limit(30)
                ->get()
        );
    }
}
