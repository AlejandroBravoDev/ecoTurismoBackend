<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LugaresController;
use App\Http\Controllers\HospedajesController;
use App\Http\Controllers\comentariosController;
use App\Http\Controllers\favoritosController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\MunicipioController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
use App\Http\Controllers\LugaresController;
use App\Http\Controllers\HospedajeController;
use App\Http\Controllers\comentariosController;
use App\Http\Controllers\favoritosController;
use App\Http\Controllers\PasswordResetController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/lugares', [LugaresController::class, 'index']);
Route::get('/lugares/{id}', [LugaresController::class, 'show']);

Route::get('/hospedajes', [HospedajeController::class, 'index']);
Route::get('/hospedajes/{id}', [HospedajeController::class, 'show']);

Route::get('/lugares', [LugaresController::class, 'index']);
Route::get('/lugares/{id}', [LugaresController::class, 'show']);
Route::get('/municipios', [MunicipioController::class, 'index']);
Route::get('/hospedajes', [HospedajesController::class, 'index']);
Route::get('/hospedajes/{id}', [HospedajesController::class, 'show']);

Route::get('/comentarios', [comentariosController::class, 'index']);
Route::get('/favoritos', [favoritosController::class, 'index']);

Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    
Route::middleware('auth:sanctum')->group(function () {
    

    Route::get('/perfil', [PerfilController::class, 'show']);
    Route::post('/perfil/update', [PerfilController::class, 'update']);

    Route::post('/comentarios', [comentariosController::class, 'store']);
    Route::delete('/comentarios/{id}', [comentariosController::class, 'destroy']);

    Route::post('/favoritos', [favoritosController::class, 'store']);
    Route::delete('/favoritos/{id}', [favoritosController::class, 'destroy']);
    Route::get('/favoritos/check/{id}', [favoritosController::class, 'check']);

    // Sesión
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/lugares', [LugaresController::class, 'store']);
    Route::put('/lugares/{id}', [LugaresController::class, 'update']);
    Route::delete('/lugares/{id}', [LugaresController::class, 'destroy']);

    Route::post('/hospedajes', [HospedajeController::class, 'store']);
    Route::put('/hospedajes/{id}', [HospedajeController::class, 'update']);
    Route::delete('/hospedajes/{id}', [HospedajeController::class, 'destroy']);
});
    Route::post('/hospedajes', [HospedajesController::class, 'store']);
    Route::put('/hospedajes/{id}', [HospedajesController::class, 'update']);
    Route::delete('/hospedajes/{id}', [HospedajesController::class, 'destroy']);

    /*Rutas protegidas*/
    Route::get('/usuario', [UsuariosController::class, 'index']);
    Route::get('/usuario/{id}', [UsuariosController::class, 'show']);
    Route::post('/usuario', [UsuariosController::class, 'store']);
    Route::put('/usuario/{id}', [UsuariosController::class, 'update']);
    Route::delete('/usuario/{id}', [UsuariosController::class, 'destroy']);
});
