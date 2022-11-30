<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OsSituacao extends Model
{
    use HasFactory;

    protected $table = "os_situacao";
    protected $primaryKey = "id_os_situacao";

    protected $visible = [
        "id_os_situacao",
        "situacao",
        "aprovada"
    ];
}
