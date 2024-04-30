<?php

namespace App\Http\Controllers;

use App\Http\Requests\TarefaEitiRequest;
use App\Http\Resources\TarefaEitiResource;
use App\Models\TarefaEiti;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EitiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $tituloHistoria = $request->get("titulo_historia");
        $descricaoHistoria = $request->get("descricao_historia");
        $osCodigo = $request->get("os_codigo");
        $projetoNome = $request->get("projeto");

        $id_projeto = DB::connection("pgsqlEiti")->table("projetos")->select("id_projeto")->where("nome", "=", $projetoNome);

        DB::connection("pgsqlEiti")->beginTransaction();

        $idProjetoHistoria = DB::connection("pgsqlEiti")->insert("
            INSERT INTO projeto_historia(
              id_projeto,
              id_projeto_participante,
              titulo,
              descricao,
              situacao,
              codigo
            ) VALUES(
                ?,
                fnc_get_id_projeto_participante(?,?),
                ?,
                ?,
                ?
             )
            RETURNING id_projeto_historia;
        ", [
            $id_projeto,
            1,
            $id_projeto,
            $tituloHistoria,
            $descricaoHistoria,
            "A",
            $osCodigo
        ]);

        DB::connection("pgsqlEiti")->insert("
            INSERT INTO projeto_historia_tarefa(
                id_projeto_historia,
                titulo,
                descricao,
                tipo,
                data_criacao,
                situacao
                ) VALUES (?, ?, ?, ?, ?, ?)
            ", [
            $idProjetoHistoria,
            $tituloHistoria,
            $descricaoHistoria,
            "B",
            now(),
            "A"
        ]);

        DB::connection("pgsqlEiti")->commit();

        return response()->json(["data" => "Tarefa criada com sucesso."]);
    }
}
