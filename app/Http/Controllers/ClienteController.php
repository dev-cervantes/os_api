<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cliente\ContainsNameRequest;
use App\Http\Resources\Cliente\ClienteCollectionResource;
use App\Http\Resources\Cliente\ClienteResource;
use App\Models\Cliente;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ClienteController extends Controller
{
    public function show(int $id): ClienteResource
    {
        return new ClienteResource(
            resource: Cliente::query()->find($id) ?? throw new BadRequestException("Cliente não encontrado.", 404)
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
