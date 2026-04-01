<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LugaresController;
use App\Http\Controllers\HospedajeController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/lugares', [LugaresController::class, 'index']);
Route::get('/lugares/{id}', [LugaresController::class, 'show']);
Route::get('/hospedajes', [HospedajeController::class, 'index']);
Route::get('/hospedajes/{id}', [HospedajeController::class, 'show']);
