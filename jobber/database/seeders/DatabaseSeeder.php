<?php

namespace Database\Seeders;

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
        // User::factory(10)->create();

//        User::factory()->create([
//            'name' => 'Chisom Ejim',
//            'email' => 'ejimchisom@gmail.com',
//        ]);

        User::query()->firstOrCreate(
            ['email' => 'ejimchisom@gmail.com'],
            ['name' => 'Chisom Ejim', 'password' => bcrypt('password')]
        );
        User::query()->firstOrCreate(
            ['email' => 'entochsoft@gmail.com'],
            ['name' => 'Praise Ejim', 'password' => bcrypt('password')]
        );
    }
}
