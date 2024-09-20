<?php

use App\Livewire\ViewPlayers;
use App\Livewire\ViewTeams;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('/teams', ViewTeams::class);
Route::get('/players', ViewPlayers::class);
