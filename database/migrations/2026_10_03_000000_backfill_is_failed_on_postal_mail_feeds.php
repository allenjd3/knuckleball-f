<?php

use App\Models\Feed;
use App\Models\PostalMail;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Feed::query()
            ->where('feedable_type', PostalMail::class)
            ->whereIn('feedable_id', PostalMail::query()->where('is_failed', true)->select('id'))
            ->chunkById(500, function ($feeds) {
                foreach ($feeds as $feed) {
                    $feed->meta = [...$feed->meta, 'is_failed' => true];
                    $feed->saveQuietly();
                }
            });
    }

    public function down(): void {}
};
