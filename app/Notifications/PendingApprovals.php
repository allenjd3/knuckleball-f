<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PendingApprovals extends Notification
{
    use Queueable;

    public function __construct(
        public int $unapprovedTeamsCount,
        public int $unapprovedPlayersCount,
        public int $unapprovedAddressesCount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->markdown('mail.pending-approvals', [
            'unapprovedAddressesCount' => $this->unapprovedAddressesCount,
            'unapprovedTeamsCount' => $this->unapprovedTeamsCount,
            'unapprovedPlayersCount' => $this->unapprovedPlayersCount,
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
