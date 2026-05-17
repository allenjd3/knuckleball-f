<?php

use App\Models\CardShop;

test('it generates a slug from the name on creation', function () {
    $shop = CardShop::factory()->create(['name' => 'The Card Shop']);

    expect($shop->slug)->toBe('the-card-shop');
});

test('it appends a numeric suffix when the slug is already taken', function () {
    CardShop::factory()->create(['name' => 'The Card Shop']);
    $shop2 = CardShop::factory()->create(['name' => 'The Card Shop']);
    $shop3 = CardShop::factory()->create(['name' => 'The Card Shop']);

    expect($shop2->slug)->toBe('the-card-shop-2');
    expect($shop3->slug)->toBe('the-card-shop-3');
});

test('it preserves a manually provided slug', function () {
    $shop = CardShop::factory()->create(['name' => 'My Shop', 'slug' => 'custom-slug']);

    expect($shop->slug)->toBe('custom-slug');
});

test('scopeApproved filters to approved shops only', function () {
    CardShop::factory()->create(['status' => 'pending']);
    CardShop::factory()->approved()->create();
    CardShop::factory()->create(['status' => 'rejected']);

    expect(CardShop::approved()->count())->toBe(1);
});
