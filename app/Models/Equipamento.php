<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipamento extends Model
{
    use HasFactory;

    protected $table = "equipamento";
    protected $primaryKey = "id_equipamento";

    protected $guarded = ["id_equipamento"];
    protected $visible = [
        "id_equipamento",
        "equipamento_codigo",
        "descricao",
        "itens"
    ];

    public $timestamps = false;

    public function itens(): HasMany
    {
        return $this->hasMany(EquipamentoItem::class, "id_equipamento", "id_equipamento");
    }
}
