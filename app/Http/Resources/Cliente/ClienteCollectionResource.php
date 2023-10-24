<?php

namespace App\Http\Resources\Cliente;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ClienteCollectionResource extends ResourceCollection
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
            "success" => true,
            "data" => $this->collection
        ];
    }
}
