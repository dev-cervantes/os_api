<?php

namespace App\Http\Resources\Equipamento;

use Illuminate\Http\Resources\Json\ResourceCollection;

class EquipamentoCollectionResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
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
