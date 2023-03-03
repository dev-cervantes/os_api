<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigOs extends Model
{
    use HasFactory;

    protected $table = "config_os";
    protected $primaryKey = null;

    protected $visible = [
        "id_os_situacao_encerrada",
        "id_os_tipo_atendimento_padrao",
        "texto_recibo_entrada",
        "texto_os",
        "id_os_situacao_aprovada",
        "id_os_situacao_padrao",
        "tipo_impressao_os",
        "permite_alterar_preco_venda_produto",
        "permite_alterar_preco_venda_servico",
        "exibir_detalhes_servico",
        "forma_autenticacao_usuario_os",
        "forma_autenticacao_usuario_servico"
    ];

    public $timestamps = false;
}
