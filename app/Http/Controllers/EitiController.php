<?php

namespace App\Http\Controllers;

use App\Models\Os;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class EitiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $tituloHistoria = $request->get("titulo_historia");
        $descricaoHistoria = $request->get("descricao_historia");
        $osCodigo = $request->get("os_codigo");
        $projetoNome = $request->get("projeto");

        $os = Os::query()->where("os_codigo", "=", "$osCodigo")->first();
        if (is_null($os))
            throw new BadRequestException("OS não encontrada!");

        $resultProjeto = DB::connection("pgsqlEiti")
            ->table("projeto")
            ->select("id_projeto")
            ->where("nome", "=", $projetoNome)
            ->first();

        if (is_null($resultProjeto))
            throw new BadRequestException("Projeto não encontrado!");

        $resultProjetoParticipante = DB::connection("pgsqlEiti")->selectOne("SELECT fnc_get_id_projeto_participante(?, ?) as id_projeto_participante", [1, $resultProjeto->id_projeto]);

        if (is_null($resultProjetoParticipante))
            throw new BadRequestException("Participante sem acesso ao projeto!");

        DB::connection("pgsqlEiti")->beginTransaction();

        $existeTarefa = DB::connection("pgsqlEiti")
            ->table("projeto_historia")
            ->whereRaw("'OS$osCodigo' = ANY (STRING_TO_ARRAY(REPLACE(UPPER(codigo), ' ', ''), ','))")
            ->exists();

        if ($existeTarefa)
            throw new BadRequestException("Já existe uma história para esta OS!");

        $idProjetoIteracao = DB::connection("pgsqlEiti")
            ->table("projeto_iteracao")
            ->where("id_projeto", "=", $resultProjeto->id_projeto)
            ->max("id_projeto_iteracao");

        if (is_null($idProjetoIteracao))
            throw new BadRequestException("Não foi possível criar a história!");

        $idProjetoHistoria = DB::connection("pgsqlEiti")
            ->table("projeto_historia")
            ->insertGetId([
                'id_projeto' => $resultProjeto->id_projeto,
                'id_projeto_participante' => $resultProjetoParticipante->id_projeto_participante,
                'id_projeto_iteracao' => $idProjetoIteracao,
                'titulo' => "$tituloHistoria (OS $osCodigo)",
                'descricao' => $descricaoHistoria,
                'situacao' => "A",
                'codigo' => "OS$osCodigo"
            ], "id_projeto_historia");

        if (is_null($idProjetoHistoria))
            throw new BadRequestException("Não foi possível criar a história!");

        DB::connection("pgsqlEiti")
            ->table("projeto_historia_tarefa")
            ->insert([
                'id_projeto_historia' => $idProjetoHistoria,
                'titulo' => $tituloHistoria,
                'descricao' => $descricaoHistoria,
                'tipo' => "B",
                'data_criacao' => now(),
                'situacao' => "A"
            ]);

        DB::connection("pgsqlEiti")->commit();

        return response()->json(["data" => "Tarefa criada com sucesso!"]);
    }
}
