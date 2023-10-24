<?php

namespace App\Http\Resources\Os;

use Illuminate\Http\Resources\Json\JsonResource;

class OsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
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
