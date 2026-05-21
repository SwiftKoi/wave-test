<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BannedPokemon extends Model
{
    use HasFactory;

    protected $table = 'banned_pokemons';

    protected $fillable = [
        'pokemon_id',
        'pokemon_name'
    ];
}
