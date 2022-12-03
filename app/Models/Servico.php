<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    use HasFactory;

    protected $table = "servico";
    protected $primaryKey = "id_servico";

    protected $visible = [
        "id_servico",
        "servico_codigo",
        "descricao",
        "preco_venda"
    ];

    public $timestamps = false;
}
