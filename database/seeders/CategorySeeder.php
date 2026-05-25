<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Baseball',
            'Basketball',
            'Football',
            'Hockey',
            'Soccer',
            'Wrestling',
            'Golf',
            'Boxing / MMA',
            'Pokemon / TCG',
            'Comic Books',
            'Non-Sports',
            'Vintage / Pre-War',
            'Graded Cards',
            'Autographs',
            'Multi-Sport',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
