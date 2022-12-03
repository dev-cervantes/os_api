<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use HasFactory;

    protected $table = "usuario";
    protected $primaryKey = "id_usuario";

    protected $hidden = ["senha"];

    protected $visible = [
        "id_usuario",
        "nome",
        "login_usuario",
        "perfil"
    ];

    public $timestamps = false;
}
