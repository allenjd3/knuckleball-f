<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            [
                'label' => 'Verified',
                'slug' => 'verified',
                'category' => 'positive',
                'description' => 'Confirmed as the legitimate signer, ensuring credibility and authenticity.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'Hall of Famer',
                'slug' => 'hall-of-famer',
                'category' => 'positive',
                'description' => 'This player has been inducted into the Hall of Fame.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'TTM Legend',
                'slug' => 'ttm-legend',
                'category' => 'positive',
                'description' => 'Consistently reliable and generous signer.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'Fan Favorite',
                'slug' => 'fan-favorite',
                'category' => 'positive',
                'description' => 'Loved for responsiveness and kindness.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'Generous Gem',
                'slug' => 'generous-gem',
                'category' => 'positive',
                'description' => 'Includes unexpected extras.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'Quick Draw',
                'slug' => 'quick-draw',
                'category' => 'positive',
                'description' => 'Lightning-fast turnaround.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'Sharpie Whisperer',
                'slug' => 'sharpie-whisperer',
                'category' => 'positive',
                'description' => 'Delivers clean, high-quality signatures.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'Gold Standard',
                'slug' => 'gold-standard',
                'category' => 'positive',
                'description' => 'Top-quality autographs and extras.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'Sticker Shock',
                'slug' => 'sticker-shock',
                'category' => 'neutral',
                'description' => 'Charges high fees.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'Pay to Play',
                'slug' => 'pay-to-play',
                'category' => 'neutral',
                'description' => 'Requires a fee for every autograph.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'The Rare Returner',
                'slug' => 'the-rare-returner',
                'category' => 'neutral',
                'description' => 'Rarely signs, making returns valuable.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'In-Person Only',
                'slug' => 'in-person-only',
                'category' => 'neutral',
                'description' => 'Signs only at events or for a fee.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'Premium Signer',
                'slug' => 'premium-signer',
                'category' => 'neutral',
                'description' => 'Only signs in-person for a high fee, making autographs rare and highly valuable.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'Under Contract',
                'slug' => 'under-contract',
                'category' => 'neutral',
                'description' => 'Only signs through exclusive deals (e.g., Topps, Upper Deck) and does not accept direct requests, even in person.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'The Wild Card',
                'slug' => 'wild-card',
                'category' => 'unpredictable',
                'description' => 'Unpredictable signing habits.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'Return Roulette',
                'slug' => 'return-roulette',
                'category' => 'unpredictable',
                'description' => 'Sometimes signs, sometimes doesn’t.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'The Snail Mailer',
                'slug' => 'the-snail-mailer',
                'category' => 'unpredictable',
                'description' => 'Takes long to respond.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'Elusive Signer',
                'slug' => 'elusive-signer',
                'category' => 'negative',
                'description' => 'Rarely signs or declines requests.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'Hard to Land',
                'slug' => 'hard-to-land',
                'category' => 'negative',
                'description' => 'Keeps cards without signing.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'Graffiti Artist',
                'slug' => 'graffiti-artist',
                'category' => 'negative',
                'description' => 'Messy or overly stylized signatures.',
                'published_at' => now()->subDay(),
            ],
            [
                'label' => 'Mystery Ink',
                'slug' => 'mystery-ink',
                'category' => 'negative',
                'description' => 'Suspected of using a ghost signer or inconsistently signing, leaving collectors uncertain.',
                'published_at' => now()->subDay(),
            ],
        ];

        collect($tags)
            ->each(fn ($tag) => Tag::create($tag));
    }
}
