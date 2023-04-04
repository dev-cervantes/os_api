<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cidade extends Model
{
    use HasFactory;

    protected $table = "cidade";
    protected $primaryKey = "id_cidade";
    public $timestamps = false;

    protected $visible = [
        "id_cidade",
        "cod_cidade",
        "descricao",
        "cod_estado",
        "sigla_estado",
        "populacao",
        "inativo"
    ];
}
