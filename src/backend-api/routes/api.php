<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EsdevenimentController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StatsController;

// Rutes públiques per a la cartellera i butaques
Route::get('/esdeveniments', [EsdevenimentController::class , 'index']);
Route::get('/esdeveniments/{id}', [EsdevenimentController::class , 'show']);

// Ruta per a consultar reserves per email
Route::get('/entrades', [ReservaController::class , 'getReservesByEmail']);

// Ruta per a processar la compra final
Route::post('/compra', [ReservaController::class , 'confirmarCompra']);

// Rutes d'Administració
Route::prefix('admin')->group(function () {
    Route::get('/esdeveniments', [AdminController::class , 'index']);
    Route::post('/esdeveniments', [AdminController::class , 'store']);
    Route::put('/esdeveniments/{id}', [AdminController::class , 'update']);
    Route::get('/esdeveniments/{id}', [AdminController::class , 'show']);
    Route::delete('/esdeveniments/{id}', [AdminController::class , 'destroy']);

    Route::get('/stats', [StatsController::class , 'index']);
});