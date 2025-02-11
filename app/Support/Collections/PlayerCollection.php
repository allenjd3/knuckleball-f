<?php

namespace App\Support\Collections;

use App\Models\Player;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class PlayerCollection extends EloquentCollection
{
    public function publishAll(): void
    {
        Player::whereIn('id', $this->pluck('id'))->update(['published_at' => now()]);
    }
}
