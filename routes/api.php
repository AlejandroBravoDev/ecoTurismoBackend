<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LugaresController;
use App\Http\Controllers\HospedajeController;
use App\Http\Controllers\comentariosController;

// Rutas Públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/lugares', [LugaresController::class, 'index']);
Route::get('/lugares/{id}', [LugaresController::class, 'show']);

Route::get('/hospedajes', [HospedajeController::class, 'index']);
Route::get('/hospedajes/{id}', [HospedajeController::class, 'show']);
Route::get('/comentarios', [ComentariosController::class, 'index']);

// Rutas Protegidas (Requieren Token)
Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/comentarios', [ComentariosController::class, 'store']);
    Route::delete('/comentarios/{id}', [ComentariosController::class, 'destroy']);

    Route::post('/logout', [AuthController::class, 'logout']);
});