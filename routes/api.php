<?php

use App\Http\Controllers\OsController;
use Illuminate\Support\Facades\Route;

Route::apiResource("os", OsController::class);
Route::get("os/codigo/{codigo}", [OsController::class, "getByCodigoOs"]);
