<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReturnCardController;
use App\Http\Controllers\SnapshotCardController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Middleware\StoreIntendedUrl;
use App\Livewire\BillingSettings;
use App\Livewire\BrowsePacks;
use App\Livewire\BrowseSets;
use App\Livewire\BrowseWantLists;
use App\Livewire\CardShopsLanding;
use App\Livewire\EventsLanding;
use App\Livewire\ShowCardShop;
use App\Livewire\ShowEvent;
use App\Livewire\ShowPack;
use App\Livewire\ShowPlayer;
use App\Livewire\ShowReturn;
use App\Livewire\ShowSet;
use App\Livewire\ShowWantList;
use App\Livewire\SubmitCardShop;
use App\Livewire\SubmitEvent;
use App\Livewire\TrendingFeed;
use App\Livewire\UserFeed;
use App\Livewire\UserProfile;
use App\Livewire\ViewCategories;
use App\Livewire\ViewPacks;
use App\Livewire\ViewPlayers;
use App\Livewire\ViewPlayersFromCategory;
use App\Livewire\ViewPlayersFromTeam;
use App\Livewire\ViewSets;
use App\Livewire\ViewTeams;
use App\Livewire\ViewWantLists;
use App\Livewire\WatchlistManager;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::view('dmca', 'dmca')->name('dmca');
Route::get('feed', UserFeed::class)->name('users.feed');
Route::get('trending', TrendingFeed::class)->name('users.trending');
Route::get('teams', ViewTeams::class)->name('teams.index');
Route::get('categories/{category}/teams', ViewTeams::class)->name('categories.teams.index');
Route::get('categories/{category}/players', ViewPlayersFromCategory::class)->name('categories.players.index');
Route::get('categories', ViewCategories::class)->name('categories.index');
Route::get('teams/{team}', ViewPlayersFromTeam::class)->name('teams.show');
Route::get('feed/{user:slug}', UserProfile::class)->name('users.profile');
Route::get('feed/{user:slug}/snapshot/{year}', [SnapshotCardController::class, 'show'])->name('users.snapshot');
Route::get('players', ViewPlayers::class)->name('players.index');
Route::get('players/{player:slug}', ShowPlayer::class)->middleware([StoreIntendedUrl::class])->name('players.show');
Route::get('packs', ViewPacks::class)->middleware(['auth'])->name('packs.index');
Route::get('packs/browse', BrowsePacks::class)->name('packs.browse');
Route::get('packs/{pack:slug}', ShowPack::class)->name('packs.show');
Route::get('sets', ViewSets::class)->middleware(['auth'])->name('sets.index');
Route::get('sets/browse', BrowseSets::class)->name('sets.browse');
Route::get('sets/{set:slug}', ShowSet::class)->name('sets.show');
Route::get('want-lists', ViewWantLists::class)->middleware(['auth'])->name('wantLists.index');
Route::get('want-lists/browse', BrowseWantLists::class)->name('wantLists.browse');
Route::get('want-lists/{wantList:slug}', ShowWantList::class)->name('wantLists.show');

// Events & Signings
Route::get('events', EventsLanding::class)->name('events.index');
Route::get('events/submit', SubmitEvent::class)->middleware(['auth'])->name('events.submit');
Route::get('events/{event}', ShowEvent::class)->name('events.show');

// Card Shop Directory
Route::get('shops', CardShopsLanding::class)->name('shops.index');
Route::get('shops/submit', SubmitCardShop::class)->middleware(['auth'])->name('shops.submit');
Route::get('shops/{shop:slug}', ShowCardShop::class)->name('shops.show');
Route::get('watchlist', WatchlistManager::class)->middleware(['auth'])->name('watchlist.index');
Route::get('billing', BillingSettings::class)->middleware(['auth'])->name('billing.index');

// Public return detail + share card download (no auth required)
Route::get('returns/{mail}', ShowReturn::class)->name('returns.show');
Route::get('returns/{mail}/card/{format}', [ReturnCardController::class, 'download'])->name('returns.card.download');

// Stripe webhook (CSRF-exempt via Cashier's built-in verification)
Route::post('stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('cashier.webhook');
