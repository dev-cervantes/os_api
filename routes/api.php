<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EquipamentoController;
use App\Http\Controllers\OsController;
use App\Http\Controllers\OsSituacaoController;
use App\Http\Controllers\OsTipoAtendimentoController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::prefix("auth")->group(function () {
    Route::post("login", [AuthController::class, "login"]);
    Route::post("logout", [AuthController::class, "logout"])->middleware("auth:api");
    Route::post("refresh-token", [AuthController::class, "refreshToken"]);
});

Route::post("os/liberar", [OsController::class, "liberar"]);
Route::resource("os", OsController::class);

Route::middleware("auth:api")->group(function () {
    Route::get("os/codigo/{codigo}", [OsController::class, "getByCodigoOs"]);

    Route::apiResource("os-situacao", OsSituacaoController::class)->only(["index"]);
    Route::apiResource("os-tipo-atendimento", OsTipoAtendimentoController::class)->only(["index"]);
    Route::apiResource("equipamento", EquipamentoController::class)->only(["index", "store"]);
    Route::apiResource("servico", ServicoController::class)->only(["index"]);
    Route::apiResource("produto", ProdutoController::class)->only(["index"]);
    Route::apiResource("usuario", UsuarioController::class)->only(["index"]);
    Route::get("cliente/contains-name", [ClienteController::class, "containsName"]);
    Route::apiResource("cliente", ClienteController::class)->only(["show"]);
});
