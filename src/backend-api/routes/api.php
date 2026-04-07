<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EsdevenimentController;
use App\Http\Controllers\ReservaController;

// Rutes públiques per a la cartellera i butaques
Route::get('/esdeveniments', [EsdevenimentController::class , 'index']);
Route::get('/esdeveniments/{id}', [EsdevenimentController::class , 'show']);

// Ruta per a processar la compra final
Route::post('/compra', [ReservaController::class , 'confirmarCompra']);