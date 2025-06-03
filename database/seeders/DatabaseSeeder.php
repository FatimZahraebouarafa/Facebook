<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // On marque toutes les migrations en attente comme exécutées
        DB::table('migrations')->insert([
            [
                'migration' => '2024_06_02_create_comments_table',
                'batch' => 2,
            ],
            [
                'migration' => '2024_06_02_create_friendships_table',
                'batch' => 2,
            ],
            [
                'migration' => '2024_06_02_create_likes_table',
                'batch' => 2,
            ],
            [
                'migration' => '2024_06_02_create_posts_table',
                'batch' => 2,
            ],
            [
                'migration' => '2024_06_02_update_users_table',
                'batch' => 2,
            ],
        ]);
        
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
