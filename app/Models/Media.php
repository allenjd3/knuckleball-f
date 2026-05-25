<?php

namespace App\Models;

use App\Actions\UpdateFeedItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::created(function (Media $media) {
            if ($media->imageable_type === Card::class) {
                $card = $media->imageable;
                if ($card?->postalMail) {
                    UpdateFeedItem::execute($card->postalMail, $card->postalMail->comment);
                }
            }
        });
    }

    public function imageable(): MorphTo
    {
        return $this->morphTo();
    }
}
