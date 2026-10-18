<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['projeto' => 'Atividade API Produtos', 'produtos' => '/api/produtos', 'consumo_externo' => '/api/catalogo-externo']));
