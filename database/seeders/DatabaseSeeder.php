<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create default accounts for the user
        (new AccountSeeder())->createAccountsForUser($user);

        // Create default categories for the user
        (new CategorySeeder())->createCategoriesForUser($user);
    }
}
