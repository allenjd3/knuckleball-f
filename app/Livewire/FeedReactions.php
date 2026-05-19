<?php

namespace App\Livewire;

use App\Models\Feed;
use App\Models\Reaction;
use App\Models\User;
use App\Notifications\NewReaction;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class FeedReactions extends Component
{
    const TYPES = ['fire', 'wow', 'trophy', 'mailbox', 'goat', 'sad'];
    const EMOJIS = ['fire' => '🔥', 'wow' => '🤩', 'trophy' => '🏆', 'mailbox' => '📬', 'goat' => '🐐', 'sad' => '😢'];

    #[Locked]
    public int $feedId;

    public function mount(Feed $feed): void
    {
        $this->feedId = $feed->id;
    }

    #[Computed]
    public function reactions()
    {
        return Reaction::where('feed_id', $this->feedId)->get();
    }

    public function counts(): array
    {
        return collect(self::TYPES)
            ->mapWithKeys(fn ($type) => [$type => $this->reactions->where('type', $type)->count()])
            ->toArray();
    }

    public function userReacted(): array
    {
        if (! auth()->check()) {
            return array_fill_keys(self::TYPES, false);
        }
        $userId = auth()->id();

        return collect(self::TYPES)
            ->mapWithKeys(fn ($type) => [
                $type => $this->reactions->where('type', $type)->where('user_id', $userId)->isNotEmpty(),
            ])
            ->toArray();
    }

    public function toggle(string $type): void
    {
        if (! auth()->check() || ! in_array($type, self::TYPES)) {
            return;
        }

        $existing = Reaction::where('feed_id', $this->feedId)
            ->where('user_id', auth()->id())
            ->first();

        $alreadyThisType = $existing?->type === $type;

        Reaction::where('feed_id', $this->feedId)
            ->where('user_id', auth()->id())
            ->delete();

        if (! $alreadyThisType) {
            Reaction::create([
                'feed_id' => $this->feedId,
                'user_id' => auth()->id(),
                'type' => $type,
            ]);

            $feed = Feed::find($this->feedId);
            $owner = $feed ? User::find($feed->followable_id) : null;
            if ($owner && $owner->id !== auth()->id()) {
                $owner->notify(new NewReaction(auth()->user(), $feed, self::EMOJIS[$type] ?? $type));
            }
        }

        unset($this->reactions);
    }

    public function render()
    {
        return view('livewire.feed-reactions', [
            'emojis' => self::EMOJIS,
            'counts' => $this->counts(),
            'userReacted' => $this->userReacted(),
        ]);
    }
}
