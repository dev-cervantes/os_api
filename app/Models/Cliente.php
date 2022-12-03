<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Cliente extends Model
{
    use HasFactory;

    protected $table = "cliente";
    protected $primaryKey = "id_cliente";
    public $timestamps = false;

    protected $visible = [
        "id_cliente",
        "nome",
        "fone",
        "razao_social",
        "apelido",
        "inativo"
    ];
}
