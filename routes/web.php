<?php

use App\Livewire\ShowPlayer;
use App\Livewire\ViewPlayers;
use App\Livewire\ViewTeams;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('teams', ViewTeams::class);
Route::get('players', ViewPlayers::class)->name('players.index');
Route::get('players/{player}', ShowPlayer::class)->name('players.show');
Route::get('test-s3-upload', function () {
    try {
        $fileName = 'test-file-' . time() . '.txt';
        $fileContents = 'This is a test file';

        // Attempt to save the file
        $path = Storage::disk('s3')->put('avatars/' . $fileName, $fileContents);

        if ($path) {
            // Check if the file exists after saving
            $exists = Storage::disk('s3')->exists('avatars/' . $fileName);

            // Get the URL of the file
            $url = Storage::disk('s3')->url('avatars/' . $fileName);

            // List all files in the avatars directory
            $files = Storage::disk('s3')->files('avatars');

            return response()->json([
                'success' => true,
                'path' => $path,
                'file_exists' => $exists,
                'url' => $url,
                'files_in_directory' => $files
            ]);
        } else {
            return response()->json(['success' => false, 'error' => 'Failed to save file']);
        }
    } catch (Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
});
