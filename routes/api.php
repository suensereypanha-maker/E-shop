<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\MenuController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Menu JSON endpoints
Route::get('/menu', [MenuController::class, 'getJson']);
Route::get('/menu.json', [MenuController::class, 'getJson']);
