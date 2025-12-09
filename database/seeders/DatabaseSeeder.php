<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Only create test user if it doesn't exist to avoid constraints errors if re-running
        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }
        
        // Seed Categories
        $categories = \App\Models\Category::factory(5)->create();

        // Seed 1000 items and assign random category
        \App\Models\Item::factory(1000)->make()->each(function ($item) use ($categories) {
            $item->category_id = $categories->random()->id;
            $item->save();
        });
    }
}
