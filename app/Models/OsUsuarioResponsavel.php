<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OsUsuarioResponsavel extends Model
{
    use HasFactory;

    protected $table = "os_usuario_responsavel_log";
    protected $primaryKey = "id_os_usuario_responsavel_log";

    protected $visible = [
        "id_os_usuario_responsavel_log",
        "id_os",
        "id_usuario_old",
        "usuario_utilizado",
        "data_hora",
    ];

    protected $casts = ["data_hora" => "datetime"];

    public $timestamps = false;

//    public static function getResponsavel(int $idOs): string | null
//    {
//        $result = @DB::select(
//            DB::raw("
//                select
//                    os_usuario_responsavel_log.*,
//                    usuario.nome
//                from os_usuario_responsavel_log
//                inner join usuario on REPLACE(usuario_utilizado, 'u_', '')::bigint = usuario.id_usuario
//                where id_os = :id_os
//                limit 1;
//            "),
//            [$idOs]
//        )[0];
//
//        return @$result->nome;
//    }
}
