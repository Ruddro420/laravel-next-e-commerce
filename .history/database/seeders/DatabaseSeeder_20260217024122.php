<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Clear data in correct order
        $this->call(ClearDataSeeder::class);
        
        // Then seed in correct order
        $this->call([
            RoleSeeder::class,        // First create roles
            PermissionSeeder::class,  // Then create permissions and assign to roles
            AdminSeeder::class,       // Then create users and assign roles
        ]);
    }
}