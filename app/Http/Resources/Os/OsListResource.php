<?php

namespace App\Http\Resources\Os;

use Illuminate\Http\Resources\Json\JsonResource;

class OsListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'codigo_os' => $this->os_codigo,
            'data-hora' => $this->data_hora->format('c'),
            'obs' => $this->obs,
            'usuario_atendente' => [
                'nome' => optional($this->usuarioAtendente)->nome,
            ],
            'equipamentos_itens' => $this->equipamentosItens->map(function ($item) {
                return [
                    'problema_reclamado' => $item->problema_reclamado,
                    'problema_constatado' => $item->problema_constatado,
                    'equipamento_item' => [
                        'identificador' => optional($item->equipamentoItem)->identificador,
                        'equipamento' => [
                            'descricao' => optional($item->equipamentoItem->equipamento)->descricao,
                        ],
                    ],
                ];
            })->all(),
        ];
    }
}
