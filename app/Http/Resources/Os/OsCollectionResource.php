<?php

namespace App\Http\Resources\Os;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OsCollectionResource extends ResourceCollection
{
    public function __construct($resource, private int $currentPage, private int $perPage, private int $total)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'success' => true,
            'data' => [
                'data' => $this->collection,
                'current_page' => $this->currentPage,
                'per_page' => $this->perPage,
                'total' => $this->total
            ],
        ];
    }
}
