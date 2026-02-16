<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Only call seeders here, NOT in migrations
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            AdminSeeder::class,
        ]);
    }
}