<?php

use App\Http\Controllers\AlbumController;
use App\Http\Controllers\FaixaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/

Route::get('/carregarDoSpotify', [AlbumController::class, 'carregarDoSpotify']);

$albunsPath = "/albuns/";
Route::get('/', [AlbumController::class, 'index']);
Route::get($albunsPath, [AlbumController::class, 'index']);
Route::get($albunsPath.'simplify', [AlbumController::class, 'indexSimplify']);
Route::get($albunsPath.'{id}', [AlbumController::class, 'show']);
Route::get($albunsPath.'{id}'.'/faixas', [AlbumController::class, 'faixas']);
Route::delete($albunsPath.'{id}'.'/faixa/'.'{faixa_id}', [AlbumController::class, 'desvincularFaixa']);
Route::post($albunsPath.'{id}'.'/faixa/'.'{faixa_id}', [AlbumController::class, 'atribuirFaixa']);
Route::post($albunsPath, [AlbumController::class, 'store']);
Route::put($albunsPath.'{id}', [AlbumController::class, 'update']);
Route::delete($albunsPath.'{id}', [AlbumController::class, 'destroy']);

$faixasPath = "/faixas/";
Route::get($faixasPath, [FaixaController::class, 'index']);
Route::get($faixasPath.'simplify', [FaixaController::class, 'indexSimplify']);
Route::get($faixasPath.'{id}', [FaixaController::class, 'show']);
Route::get($faixasPath.'{id}'.'/albuns', [FaixaController::class, 'albuns']);
Route::post($faixasPath, [FaixaController::class, 'store']);
Route::put($faixasPath.'{id}', [FaixaController::class, 'update']);
Route::delete($faixasPath.'{id}', [FaixaController::class, 'destroy']);