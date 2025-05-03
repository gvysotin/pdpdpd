<?php

namespace Database\Seeders;

use App\Events\Registration\UserRegistered;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(1000)->create();

        // foreach (User::factory()->count(1000)->make() as $user) {
        //     $user->save();
        //     event(new UserRegistered($user));
        // }



        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
