<?php

namespace App\Console\Commands\Notification;

use App\Models\Address;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use App\Notifications\PendingApprovals as NotificationsPendingApprovals;
use Illuminate\Console\Command;

class PendingApprovals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:pending-approvals';

    protected $description = 'Notifies admins about pending approvals';

    public function handle()
    {
        $unapprovedPlayersCount = Player::query()
            ->whereNull('published_at')
            ->where('rejected_at', false)
            ->count();

        $unapprovedAddressesCount = Address::query()
            ->whereNull('published_at')
            ->where('rejected_at', false)
            ->count();

        $unapprovedTeamsCount = Team::query()
            ->whereNull('published_at')
            ->where('rejected_at', false)
            ->count();

        User::where('super_admin', true)->get()
            ->each(
                fn ($user) => $user->notify(
                    new NotificationsPendingApprovals($unapprovedTeamsCount, $unapprovedAddressesCount, $unapprovedPlayersCount)
                )
            );
    }
}
