<?php

namespace Database\Seeders\Production;

use App\Actions\CreateFeedItem;
use App\Models\Feed;
use App\Models\PostalMail;
use Illuminate\Database\Seeder;

class FeedSeeder extends Seeder
{
    public function run(): void
    {
        PostalMail::get()
            ->each(
                fn (PostalMail $postalMail) => CreateFeedItem::execute(
                    feedItem: $postalMail,
                    comment: $postalMail->comment,
                ),
            );

        for ($x = 0; $x < 10; $x++) {
            Feed::factory()->comment()->create();
        }
    }
}
