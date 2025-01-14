<?php

use App\Livewire\ShowPlayer;
use App\Livewire\UserFeed;
use App\Livewire\UserProfile;
use App\Livewire\ViewPlayers;
use App\Livewire\ViewPlayersFromTeam;
use App\Livewire\ViewTeams;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('teams', ViewTeams::class)->name('teams.index');
Route::get('teams/{team}', ViewPlayersFromTeam::class)->name('teams.show');
Route::get('feed', UserFeed::class)->name('users.feed');
Route::get('users/{user}/profile', UserProfile::class)->name('users.profile');
Route::get('players', ViewPlayers::class)->name('players.index');
Route::get('players/{player}', ShowPlayer::class)->name('players.show');
