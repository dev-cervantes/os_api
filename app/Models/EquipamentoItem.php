<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipamentoItem extends Model
{
    use HasFactory;

    protected $table = "equipamento_item";
    protected $primaryKey = "id_equipamento_item";

    protected $visible = [
        "id_equipamento_item",
        "identificador",
        "obs",
        "id_equipamento",
        "equipamento"
    ];

    public function equipamento(): BelongsTo
    {
        return $this->belongsTo(Equipamento::class, "id_equipamento", "id_equipamento");
    }
}
