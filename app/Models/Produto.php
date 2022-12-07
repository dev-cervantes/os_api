<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    public const scopeWhereNotInativo = "notInativo";

    static function boot()
    {
        parent::boot();

        static::addGlobalScope(self::scopeWhereNotInativo, function (Builder $builder) {
            $builder->where("inativo", "=", false);
        });
    }
}
