<?php

namespace App\Livewire;

use Livewire\Attributes\Computed;
use Livewire\Component;

class NotificationBell extends Component
{
    public bool $open = false;

    #[Computed]
    public function notifications()
    {
        if (! auth()->check()) {
            return collect();
        }
        return auth()->user()->notifications()->latest()->limit(20)->get();
    }

    #[Computed]
    public function unreadCount(): int
    {
        if (! auth()->check()) {
            return 0;
        }
        return auth()->user()->unreadNotifications()->count();
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
        if ($this->open) {
            unset($this->notifications, $this->unreadCount);
        }
    }

    public function markAllRead(): void
    {
        auth()->user()?->unreadNotifications->markAsRead();
        unset($this->notifications, $this->unreadCount);
    }

    public function markRead(string $id): void
    {
        auth()->user()?->notifications()->where('id', $id)->first()?->markAsRead();
        unset($this->notifications, $this->unreadCount);
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
