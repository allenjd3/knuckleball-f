<?php
namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserSnapshot;
use App\Services\UserStatsService;
use Illuminate\Console\Command;

class TakeUserSnapshots extends Command
{
    protected $signature = 'snapshots:take {--year= : Year to snapshot (defaults to current year)}';
    protected $description = 'Freeze the current stats for every user into a yearly snapshot';

    public function handle(UserStatsService $statsService): void
    {
        $year = (int) ($this->option('year') ?? now()->year);
        $users = User::all();

        $this->withProgressBar($users, function (User $user) use ($year, $statsService) {
            UserSnapshot::updateOrCreate(
                ['user_id' => $user->id, 'year' => $year],
                ['data' => $statsService->compute($user)]
            );
        });

        $this->newLine();
        $this->info("Snapshots taken for {$users->count()} users ({$year}).");
    }
}
