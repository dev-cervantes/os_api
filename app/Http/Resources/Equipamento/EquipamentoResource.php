<?php

namespace App\Http\Resources\Equipamento;

use Illuminate\Http\Resources\Json\JsonResource;

class EquipamentoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'success' => true,
            'data' => parent::toArray($request)
        ];
    }
}
