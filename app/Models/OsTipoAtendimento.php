<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OsTipoAtendimento extends Model
{
    use HasFactory;

    protected $table = "os_tipo_atendimento";
    protected $primaryKey = "id_os_tipo_atendimento";

    protected $visible = [
        "id_os_tipo_atendimento",
        "tipo_atendimento"
    ];

    public $timestamps = false;
}
