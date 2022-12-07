<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EquipamentoController;
use App\Http\Controllers\OsController;
use App\Http\Controllers\OsSituacaoController;
use App\Http\Controllers\OsTipoAtendimentoController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\ServicoController;
use App\Http\Middleware\ValidateSecretIsValid;
use Illuminate\Support\Facades\Route;

Route::middleware([ValidateSecretIsValid::class])->group(function () {
    Route::group(["prefix" => "auth"], function () {
        Route::post("login", [AuthController::class, "login"]);
        Route::post("logout", [AuthController::class, "logout"]);
        Route::get("refresh-token", [AuthController::class, "refreshToken"]);
    });

    Route::apiResource("os", OsController::class);
    Route::get("os/codigo/{codigo}", [OsController::class, "getByCodigoOs"]);

    Route::apiResource("os-situacao", OsSituacaoController::class)->only(["index"]);
    Route::apiResource("os-tipo-atendimento", OsTipoAtendimentoController::class)->only(["index"]);
    Route::apiResource("equipamento", EquipamentoController::class)->only(["index", "store"]);
    Route::apiResource("servico", ServicoController::class)->only(["index"]);
    Route::apiResource("produto", ProdutoController::class)->only(["index"]);
});

