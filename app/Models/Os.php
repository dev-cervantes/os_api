<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Os extends Model
{
    use HasFactory;

    protected $table = "os";
    protected $primaryKey = "id_os";

    protected $visible = [
        "id_os",
        "os_codigo",
        "data_hora",
        "data_hora_previsao_entrega",
        "data_hora_entrega",
        "data_hora_aprovacao",
        "data_hora_encerramento",
        "nome_contato",
        "fone_contato",
        "obs",
        "valor_total",
        "valor_outras_despesas",
        "inativo",
        "id_os_situacao",
        "id_os_tipo_atendimento",
        "id_cliente",
        "id_usuario_atendente",
        "id_usuario_aprovacao",
        "id_usuario_encerramento",
        "situacao",
        "tipoAtendimento",
        "cliente",
        "usuarioAtendente",
        "usuarioAprovacao",
        "usuarioEncerramento",
        "equipamentosItens",
    ];

    protected $casts = [
        "data_hora" => "datetime",
        "data_hora_previsao_entrega" => "datetime",
        "data_hora_entrega" => "datetime",
        "data_hora_aprovacao" => "datetime",
        "data_hora_encerramento" => "datetime"
    ];

    static function boot()
    {
        parent::boot();

        static::addGlobalScope('defaultRelations', function (Builder $builder) {
            $builder->with(["situacao", "tipoAtendimento", "cliente"]);
        });
    }

    public function situacao(): BelongsTo
    {
        return $this->belongsTo(OsSituacao::class, "id_os_situacao", "id_os_situacao");
    }

    public function tipoAtendimento(): BelongsTo
    {
        return $this->belongsTo(OsTipoAtendimento::class, "id_os_tipo_atendimento", "id_os_tipo_atendimento");
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, "id_cliente", "id_cliente");
    }

    public function usuarioAtendente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, "id_usuario_atendente", "id_usuario");
    }

    public function usuarioAprovacao(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, "id_usuario_aprovacao", "id_usuario");
    }

    public function usuarioEncerramento(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, "id_usuario_encerramento", "id_usuario");
    }

    public function equipamentosItens(): HasMany
    {
        return $this->hasMany(OsEquipamentoItem::class, "id_os", "id_os");
    }

    public function scopeAllRelations(Builder $query)
    {
        $query->with([
            "situacao",
            "tipoAtendimento",
            "cliente",
            "usuarioAtendente",
            "usuarioAprovacao",
            "usuarioEncerramento",
            "equipamentosItens"
        ]);
    }
}
