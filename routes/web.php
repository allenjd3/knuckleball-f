<?php

use App\Livewire\CreateAddress;
use App\Livewire\ShowPlayer;
use App\Livewire\ViewPlayers;
use App\Livewire\ViewTeams;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('teams', ViewTeams::class);
Route::get('players', ViewPlayers::class);
Route::get('players/{player}', ShowPlayer::class)->name('players.show');
