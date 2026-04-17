<?php

use App\Http\Middleware\StoreIntendedUrl;
use App\Livewire\ShowPlayer;
use App\Livewire\UserFeed;
use App\Livewire\UserProfile;
use App\Livewire\ViewCategories;
use App\Livewire\ViewPlayers;
use App\Livewire\ViewPlayersFromCategory;
use App\Livewire\ViewPlayersFromTeam;
use App\Livewire\ViewTeams;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');
Route::view('dmca', 'dmca')->name('dmca');
Route::livewire('feed', UserFeed::class)->name('users.feed');
Route::livewire('teams', ViewTeams::class)->name('teams.index');
Route::livewire('categories/{category}/teams', ViewTeams::class)->name('categories.teams.index');
Route::livewire('categories/{category}/players', ViewPlayersFromCategory::class)->name('categories.players.index');
Route::livewire('categories', ViewCategories::class)->name('categories.index');
Route::livewire('teams/{team}', ViewPlayersFromTeam::class)->name('teams.show');
Route::livewire('feed/{user:slug}', UserProfile::class)->name('users.profile');
Route::livewire('players', ViewPlayers::class)->name('players.index');
Route::livewire('players/{player:slug}', ShowPlayer::class)->middleware([StoreIntendedUrl::class])->name('players.show');
