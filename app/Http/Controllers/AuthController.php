<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        try {
            $credenciais = $request->only("login_usuario", "senha");

            $validate = $this->validator($credenciais, $this->rules(), $this->messages());
            if ($validate->fails()) {
                throw new Exception($validate->errors()->first(), 422);
            }

            $token = Auth::attempt($credenciais);
            if (!$token) {
                throw new Exception("Não autorizado.", 401);
            }

            $user = Auth::user();
            return $this->sendResponse([
                "user" => $user,
                "access_token" => $token
            ]);
        } catch (Exception $e) {
            return $this->sendResponseError($e->getMessage(), $e->getCode());
        }
    }

    public function logout(): JsonResponse
    {
        Auth::logout();
        return $this->sendResponse([]);
    }

    public function refreshToken(): JsonResponse
    {
        $token = Auth::refresh();
        return $this->sendResponse(["refresh_token" => $token]);
    }

    protected function rules(): array
    {
        return [
            "login_usuario" => "required",
            "senha" => "required"
        ];
    }

    protected function messages(): array
    {
        return [
            "login_usuario" => "Nome de usuário não informado.",
            "senha" => "Senha não informada."
        ];
    }
}
