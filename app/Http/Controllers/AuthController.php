<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\TokenRefreshedResource;
use App\Http\Resources\Auth\UserAuthenticatedResource;
use App\Http\Resources\Auth\UserLoggedResource;
use Exception;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;


class AuthController extends Controller
{
    /**
     * @throws Exception
     */
    public function login(LoginRequest $request): UserAuthenticatedResource
    {
        $token = auth()->attempt($request->all());

        if (!$token)
            throw new BadRequestException("Não autorizado.", 401);

        return new UserAuthenticatedResource(
            usuario: auth()->user(),
            token: $token
        );
    }

    public function logout(): UserLoggedResource
    {
        auth()->logout();

        return new UserLoggedResource();
    }

    public function refreshToken(): TokenRefreshedResource
    {
        $token = auth()->refresh();

        return new TokenRefreshedResource(token: $token);
    }
}
