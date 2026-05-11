<?php

use App\Http\Middleware\StoreIntendedUrl;
use App\Livewire\BrowseWantLists;
use App\Livewire\ShowPlayer;
use App\Livewire\ShowWantList;
use App\Livewire\UserFeed;
use App\Livewire\UserProfile;
use App\Livewire\ViewCategories;
use App\Livewire\ViewPlayers;
use App\Livewire\ViewPlayersFromCategory;
use App\Livewire\ViewPlayersFromTeam;
use App\Livewire\ViewTeams;
use App\Livewire\ViewWantLists;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');
Route::get('feed', UserFeed::class)->name('users.feed');
Route::get('teams', ViewTeams::class)->name('teams.index');
Route::get('categories/{category}/teams', ViewTeams::class)->name('categories.teams.index');
Route::get('categories/{category}/players', ViewPlayersFromCategory::class)->name('categories.players.index');
Route::get('categories', ViewCategories::class)->name('categories.index');
Route::get('teams/{team}', ViewPlayersFromTeam::class)->name('teams.show');
Route::get('feed/{user:slug}', UserProfile::class)->name('users.profile');
Route::get('players', ViewPlayers::class)->name('players.index');
Route::get('players/{player:slug}', ShowPlayer::class)->middleware([StoreIntendedUrl::class])->name('players.show');
Route::get('want-lists', ViewWantLists::class)->middleware(['auth'])->name('wantLists.index');
Route::get('want-lists/browse', BrowseWantLists::class)->name('wantLists.browse');
Route::get('want-lists/{wantList:slug}', ShowWantList::class)->name('wantLists.show');
