<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cliente extends Model
{
    use HasFactory;

    protected $table = "cliente";
    protected $primaryKey = "id_cliente";
    public $timestamps = false;

    protected $visible = [
        "id_cliente",
        "nome",
        "apelido",
        "razao_social",
        "pessoa_fisica",
        "cnpj",
        "cpf",
        "insc_estadual",
        "id_cidade",
        "cidade",
        "logradouro",
        "numero",
        "complemento",
        "bairro",
        "cep",
        "email",
        "fone",
        "obs",
        "inativo",
    ];

    static function boot()
    {
        parent::boot();

        static::addGlobalScope("defaultRelations", function (Builder $builder) {
            $builder->with("cidade");
        });
    }

    public function cidade(): HasOne
    {
        return $this->hasOne(Cidade::class, "id_cidade", "id_cidade");
    }
}
