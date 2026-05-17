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
        $year  = (int) ($this->option('year') ?? now()->year);
        $total = User::count();
        $count = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        User::chunk(200, function ($users) use ($year, $statsService, $bar, &$count) {
            foreach ($users as $user) {
                UserSnapshot::updateOrCreate(
                    ['user_id' => $user->id, 'year' => $year],
                    ['data' => $statsService->compute($user)]
                );
                $count++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("Snapshots taken for {$count} users ({$year}).");
    }
}
