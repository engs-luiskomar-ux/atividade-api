<?php

use App\Http\Controllers\Api\CatalogoExternoController;
use App\Http\Controllers\Api\ProdutoController;
use Illuminate\Support\Facades\Route;

Route::apiResource('produtos', ProdutoController::class);

// Consumo de uma API gratuita online, sem autenticação.
Route::get('catalogo-externo', [CatalogoExternoController::class, 'index']);
Route::get('catalogo-externo/{id}', [CatalogoExternoController::class, 'show'])->whereNumber('id');
