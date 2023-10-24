<?php

namespace App\Http\Resources\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAuthenticatedResource extends JsonResource
{
    public function __construct(private Authenticatable $usuario, private string $token, $resource = null)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|\JsonSerializable
     */
    public function toArray($request): array|\JsonSerializable|Arrayable
    {
        return [
            'success' => true,
            'data' => [
                'user' => $this->usuario,
                'access_token' => $this->token
            ]
        ];
    }
}
