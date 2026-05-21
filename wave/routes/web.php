<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BannedPokemonController;

Route::get('/', function () {
    return view('welcome');
});
