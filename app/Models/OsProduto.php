<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsProduto extends Model
{
    use HasFactory;

    protected $table = "os_produto";
    protected $primaryKey = "id_os_produto";

    protected $guarded = ["id_os_produto"];
    protected $visible = [
        "id_os_produto",
        "qtd",
        "valorTotal",
        "id_os_equipamento_item",
        "id_produto",
        "produto"
    ];

    public $timestamps = false;

    static function boot()
    {
        parent::boot();

        static::addGlobalScope("defaultRelations", function (Builder $builder) {
            $builder->with("produto");
        });
    }

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, "id_produto", "id_produto");
    }
}
