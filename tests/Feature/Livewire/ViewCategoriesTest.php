<?php

use App\Livewire\ViewCategories;
use App\Models\Category;

test('Unauthenticated users can view categories', function () {
    $this->get('categories')->assertOk();
});

test('Categories are listed alphabetically by default', function () {
    $categories = Category::factory(10)->create();

    Livewire::test(ViewCategories::class)
        ->assertCanSeeTableRecords($categories->sortBy('name'), inOrder: true);
});

test('Categories can be sorted', function () {
    $categories = Category::factory(10)->create();

    Livewire::test(ViewCategories::class)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords($categories->sortByDesc('name'), inOrder: true);
});
