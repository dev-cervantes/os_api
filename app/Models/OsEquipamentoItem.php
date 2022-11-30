<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OsEquipamentoItem extends Model
{
    use HasFactory;

    protected $table = "os_equipamento_item";
    protected $primaryKey = "id_os_equipamento_item";

    protected $visible = [
        "id_os_equipamento_item",
        "problema_reclamado",
        "problema_constatado",
        "obs",
        "id_os",
        "id_equipamento_item",
        "equipamentoItem",
        "servicos",
        "produtos"
    ];

    public function equipamentoItem(): BelongsTo
    {
        return $this->belongsTo(EquipamentoItem::class, "id_equipamento_item", "id_equipamento_item");
    }

    public function servicos(): HasMany
    {
        return $this->hasMany(OsServico::class, "id_os_equipamento_item", "id_os_equipamento_item");
    }

    public function produtos(): HasMany
    {
        return $this->hasMany(OsProduto::class, "id_os_equipamento_item", "id_os_equipamento_item");
    }
}
