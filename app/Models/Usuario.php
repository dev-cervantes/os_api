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
        "login_usuario",
        "perfil",
        "inativo",
        "nome",
        "acesso_os",
        "login_habilitado",
    ];

    protected $hidden = [
        "senha",
    ];

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
