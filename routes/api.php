<?php

use App\Http\Controllers\OsController;
use App\Models\Os;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource("os", OsController::class);
