<?php

use App\Actions\CreateShopSpotlightFeedItem;
use App\Models\CardShop;
use App\Models\Feed;
use App\Models\User;

test('it creates a feed item for the shop', function () {
    $shop = CardShop::factory()->approved()->create();

    CreateShopSpotlightFeedItem::execute($shop);

    $this->assertDatabaseHas('feeds', [
        'feedable_type' => CardShop::class,
        'feedable_id' => $shop->id,
        'followable_id' => $shop->user_id,
    ]);
});

test('it creates a feed item with null followable_id when the shop has no user', function () {
    $shop = CardShop::factory()->approved()->create(['user_id' => null]);

    CreateShopSpotlightFeedItem::execute($shop);

    $this->assertDatabaseHas('feeds', [
        'feedable_type' => CardShop::class,
        'feedable_id' => $shop->id,
        'followable_id' => null,
    ]);
});

test('it attaches to a recent digest from the same user', function () {
    $user = User::factory()->create();
    $shop = CardShop::factory()->approved()->for($user)->create();

    $digest = Feed::create([
        'followable_id' => $user->id,
        'feedable_type' => CardShop::class,
        'feedable_id' => $shop->id,
        'comment' => '',
        'meta' => array_merge($shop->generateMeta(), ['shop_ids' => [$shop->id]]),
    ]);

    $shop2 = CardShop::factory()->approved()->for($user)->create();

    CreateShopSpotlightFeedItem::execute($shop2);

    expect(Feed::count())->toBe(1);
    expect($digest->fresh()->meta['shop_ids'])->toContain($shop2->id);
});

test('it creates a new feed if the digest is older than 60 minutes', function () {
    $user = User::factory()->create();
    $shop = CardShop::factory()->approved()->for($user)->create();

    Feed::create([
        'followable_id' => $user->id,
        'feedable_type' => CardShop::class,
        'feedable_id' => $shop->id,
        'comment' => '',
        'meta' => array_merge($shop->generateMeta(), ['shop_ids' => [$shop->id]]),
        'created_at' => now()->subMinutes(61),
    ]);

    $shop2 = CardShop::factory()->approved()->for($user)->create();

    CreateShopSpotlightFeedItem::execute($shop2);

    expect(Feed::count())->toBe(2);
});
