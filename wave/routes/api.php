<?php

use App\Http\Controllers\Api\BannedPokemonController;
use App\Http\Controllers\Api\PokemonInfoController;

use Illuminate\Support\Facades\Route;

Route::middleware('super.secret')->group(function () {
    Route::get('/banned', [BannedPokemonController::class, 'index']);
    Route::post('/banned', [BannedPokemonController::class, 'store']);
    Route::delete('/banned/{bannedPokemon}', [BannedPokemonController::class, 'destroy']);
    Route::post('/info', PokemonInfoController::class);
});
