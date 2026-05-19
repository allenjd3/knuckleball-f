<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSnapshot;
use App\Services\UserStatsService;

class SnapshotCardController extends Controller
{
    public function show(User $user, int $year, UserStatsService $statsService)
    {
        abort_unless($year >= 2020 && $year <= now()->year, 404);

        $snapshot = UserSnapshot::where('user_id', $user->id)->where('year', $year)->first();

        abort_if(! $snapshot && $year < now()->year, 404);

        $stats = $snapshot ? $snapshot->data : $statsService->compute($user);

        return view('snapshot-card', compact('user', 'year', 'stats'));
    }
}
