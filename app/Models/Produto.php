<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $table = "produto";
    protected $primaryKey = "id_produto";

    protected $visible = [
        "id_produto",
        "descricao"
    ];

    public $timestamps = false;
}
