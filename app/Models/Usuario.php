<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = "usuario";
    protected $primaryKey = "id_usuario";

    protected $visible = [
        "id_usuario",
        "nome",
        "login_usuario",
        "perfil"
    ];

    public $timestamps = false;

    public function getAuthIdentifier()
    {
        return $this->login_usuario;
    }

    public function getAuthIdentifierName()
    {
        return "login_usuario";
    }

    public function getAuthPassword()
    {
        return $this->senha;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            "id_usuario" => $this->id_usuario,
            "login_usuario" => $this->login_usuario,
            "nome" => $this->nome,
            "perfil" => $this->perfil
        ];
    }
}
