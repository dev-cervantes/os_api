<?php

namespace App\Http\Controllers;

use App\Http\Requests\Os\FilterRequest;
use App\Http\Requests\Os\OsStoreRequest;
use App\Http\Requests\Os\OsUpdateRequest;
use App\Http\Resources\Os\OsCollectionResource;
use App\Http\Resources\Os\OsResource;
use App\Http\Resources\Os\OsListResource;
use App\Models\ConfigOs;
use App\Models\Os;
use App\Models\OsEquipamentoItem;
use App\Models\OsProduto;
use App\Models\OsServico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class OsController extends Controller
{
    const PAGINATOR_PER_PAGE_DEFAULT = 100;

    public function index(FilterRequest $request): OsCollectionResource
    {
        $os = Cache::remember(
            key: "os_index_" . $request->collect()->transform(fn($value, $key) => "$key->$value")->values()->join('&'),
            ttl: 15,
            callback: function () use ($request) {
                $query = Os::query();

                if ($request->has('data_inicial'))
                    $query->where("data_hora", ">=", $request->get('data_inicial'));

                if ($request->has('data_final'))
                    $query->where("data_hora", "<=", $request->get('data_final'));

                if ($request->has('codigo'))
                    $query->where("os_codigo", "=", $request->get('codigo'));

                if ($request->get('situacao_em_aberto', false)) {
                    $config = ConfigOs::query()->first();
                    $query->where("id_os_situacao", "<>", $config->id_os_situacao_encerrada);
                } else if ($request->has('situacao')) {
                    $query->where("id_os_situacao", "=", $request->get('situacao'));
                }

                if ($request->has('cliente'))
                    $query->whereHas("cliente", fn($q) => $q->where("nome", "=", $request->get('cliente')));

                if ($request->has('responsavel'))
                    $query->whereHas("responsavel", fn($q) => $q->where("nome", "=", $request->get('responsavel')));

                if ($request->hasAny(['equipamento', 'equipamento_item'])) {
                    $query->whereHas(
                        "equipamentosItens",
                        function ($equipamentosItens) use ($request) {
                            $equipamentosItens->whereHas("equipamentoItem", function ($equipamentoItem) use ($request) {
                                if ($request->has('equipamento_item'))
                                    $equipamentoItem->where("id_equipamento_item", "=", $request->get('equipamento_item'));

                                if ($request->has('equipamento'))
                                    $equipamentoItem->whereHas("equipamento", fn($equipamento) => $equipamento->where("id_equipamento", "=", $request->get('equipamento')));
                            });
                        }
                    );
                }

                $query->orderBy("os_codigo", "desc");

                return $query->paginate($request->get('per_page', self::PAGINATOR_PER_PAGE_DEFAULT));
            }
        );

        return new OsCollectionResource(
            resource: $os,
            currentPage: $os->currentPage(),
            perPage: $os->perPage(),
            total: $os->total()
        );
    }

    public function store(OsStoreRequest $request): OsResource
    {
        DB::beginTransaction();

        $os = Os::create($request->except('os_codigo'));

        foreach ($request->get('equipamentos_itens') as $equipamentoItens) {
            $equipamentoItens['id_os'] = $os->id_os;
            $osEquipamentoItens = OsEquipamentoItem::create($equipamentoItens);

            foreach ($equipamentoItens['servicos'] as $servicos) {
                $servicos['id_os_equipamento_item'] = $osEquipamentoItens->id_os_equipamento_item;
                OsServico::create($servicos);
            }

            foreach ($equipamentoItens['produtos'] as $produtos) {
                $produtos['id_os_equipamento_item'] = $osEquipamentoItens->id_os_equipamento_item;
                OsProduto::create($produtos);
            }
        }

        DB::commit();

        return new OsResource(
            resource: Os::allRelations()->find($os->id_os)
        );
    }

    public function show(int $id): OsResource
    {
        $os = Cache::remember(key: "os_show_$id", ttl: 15, callback: fn() => Os::query()
            ->withoutGlobalScope('defaultRelations')
            ->allRelations()
            ->find($id));

        return new OsResource(
            resource: $os ?? throw new BadRequestException("OS não encontrada.", 404)
        );
    }

    public function update(OsUpdateRequest $request, int $id): OsResource
    {
        DB::beginTransaction();

        $os = Os::allRelations()->find($id) ?? throw new BadRequestException("OS não encontrada!");
        $osEquipamentosItens = $os->equipamentosItens;

        $os->fill($request->all());
        $os->save();

        //Salva os novos equipamentos itens.
        foreach ($request->get('equipamentos_itens') as $reqEquipamentoItem) {
            if (!isset($reqEquipamentoItem['id_os_equipamento_item'])) {
                $osEquipamentoItem = OsEquipamentoItem::create($reqEquipamentoItem);

                foreach ($reqEquipamentoItem['servicos'] as $servicos) {
                    $servicos['id_os_equipamento_item'] = $osEquipamentoItem->id_os_equipamento_item;
                    OsServico::create($servicos);
                }

                foreach ($reqEquipamentoItem['produtos'] as $produtos) {
                    $produtos['id_os_equipamento_item'] = $osEquipamentoItem->id_os_equipamento_item;
                    OsProduto::create($produtos);
                }
            }
        }

        //Verifica os equipamentos itens para atualizar ou deletar.
        foreach ($osEquipamentosItens as $osEquipamentoItem) {
            $existe = false;

            //Percorre os equipamentos contidos na request.
            foreach ($request->get('equipamentos_itens') as $reqEquipamentoItem) {
                if ($osEquipamentoItem->id_os_equipamento_item == @$reqEquipamentoItem['id_os_equipamento_item']) {
                    $existe = true;

                    //Percorre os serviços verificando se tem algum novo para inserir no devido equipamento.
                    foreach ($reqEquipamentoItem['servicos'] as $reqServicos) {
                        if (!isset($reqServicos['id_os_servico'])) {
                            $reqServicos['id_os_equipamento_item'] = $osEquipamentoItem->id_os_equipamento_item;
                            OsServico::create($reqServicos);
                        }
                    }

                    //Percorre os serviços verificando se tem algum novo para inserir no devido equipamento.
                    foreach ($reqEquipamentoItem['produtos'] as $reqProdutos) {
                        if (!isset($reqProdutos['id_os_servico'])) {
                            $reqProdutos['id_os_equipamento_item'] = $osEquipamentoItem->id_os_equipamento_item;
                            OsServico::create($reqProdutos);
                        }
                    }

                    //Verifica os serviços para atualizar ou deletar.
                    foreach ($osEquipamentoItem->servicos as $osServico) {
                        $existeServico = false;

                        //Percorre os serviços contidos na request.
                        foreach ($reqEquipamentoItem['servicos'] as $reqServicos) {
                            if ($osServico->id_os_servico == @$reqServicos['id_os_servico']) {
                                $existeServico = true;

                                $osServico->fill($reqServicos);
                                $osServico->save();
                            }
                        }

                        if (!$existeServico) {
                            $osServico->delete();
                        }
                    }

                    //Verifica os produtos para atualizar ou deletar.
                    foreach ($osEquipamentoItem->produtos as $osProduto) {
                        $existeProduto = false;

                        //Percorre os produtos contidos na request.
                        foreach ($reqEquipamentoItem['produtos'] as $reqProduto) {
                            if ($osProduto->id_os_servico == @$reqEquipamentoItem['id_os_produto']) {
                                $existeProduto = true;

                                $osProduto->fill($reqProduto);
                                $osProduto->save();
                            }
                        }

                        if (!$existeProduto) {
                            $osProduto->delete();
                        }
                    }

                    $osEquipamentoItem->fill($reqEquipamentoItem);
                    $osEquipamentoItem->save();
                }
            }

            if (!$existe) {
                $osEquipamentoItem->delete();
            }
        }

        DB::commit();

        Cache::forget("os_show_$id");

        return new OsResource(
            resource: Os::allRelations()->find($id)
        );
    }

    public function liberar(Request $request): JsonResponse
    {
        //Valida e garante os dados recebidos pela requisição HTTP
        $request->validate(['oss' => 'required|array', 'problema_constatado' => 'required|string']);

        $problemaConstatado = $request->get('problema_constatado');

        //retira o "OS" e espaços das Oss e converte para int
        $reqOss = array();

        foreach ($request->get('oss') as $os) {
            $osInt = trim(str_replace("OS", "", $os));
            $reqOss[] = (int)$osInt;
        }

        //Busca todas as OS
        $OSs = Os::query()->whereIn('os_codigo', $reqOss)->get();

        //Inicia uma transação
        DB::beginTransaction();

        foreach ($OSs as $os) {
            // Ignora se o usuário for diferente de Desenvolvimento, para não liberar mais de uma vez
            if ($os->id_usuario_responsavel != 72) {
                continue;
            }

            //Faz as mudanças na OS
            $os->id_os_situacao = 26;

            // id - João
            $os->id_usuario_responsavel = 25;

            // Atualiza o problema_constatado na tabela OsEquipamentoItem
            foreach ($os->equipamentosItens as $item) {
                $item->problema_constatado = $problemaConstatado . "\n" . $item->problema_constatado;

                $item->save();
            }

            //Salva a OS
            $os->save();
        }

        DB::commit();

        return response()->json(["data" => "OS's liberadas com sucesso."]);
    }

    public function destroy(int $id): JsonResponse
    {
        $os = Os::query()->find($id) ?? throw new BadRequestException("OS não encontada!");
        $os->delete();

        return response()->json(null, 201);
    }

    public function getByCodigoOs(int $codigo): OsResource
    {
        $os = Cache::remember(
            key: "os_getByCodigoOs_$codigo",
            ttl: 15,
            callback: fn() => Os::query()->allRelations()->where("os_codigo", "=", "$codigo")->first()
        );

        if (is_null($os)) throw new BadRequestException("OS não encontrada!");

        return new OsResource(resource: $os);
    }

    public function listOrSearch(Request $request)
    {
        $query = Os::with([
            'usuarioAtendente',
            'equipamentosItens.equipamentoItem.equipamento'
        ]);

        if ($termoBusca = $request->get('termoBusca')) {
            $searchTerms = explode(' ', $termoBusca);

            $query->where(function ($mainQuery) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $mainQuery->where(function ($subQuery) use ($term) {
                        $subQuery->orWhere('obs', 'LIKE', '%' . $term . '%')
                            ->orWhere('os_codigo', 'LIKE', '%' . $term . '%')
                            ->orWhereHas('usuarioAtendente', function ($query) use ($term) {
                                $query->where('nome', 'LIKE', '%' . $term . '%');
                            })
                            ->orWhereHas('equipamentosItens', function ($query) use ($term) {
                                $query->where('problema_reclamado', 'LIKE', '%' . $term . '%')
                                    ->orWhere('problema_constatado', 'LIKE', '%' . $term . '%');
                            });
                    });
                }
            });
        }

        $perPage = $request->get('per_page', 100);
        $osCollection = $query->paginate($perPage);

        return OsListResource::collection($osCollection)->additional([
            'success' => true,
        ]);
    }
}
