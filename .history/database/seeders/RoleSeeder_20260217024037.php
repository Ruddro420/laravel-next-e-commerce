<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Clear existing data
        DB::table('role_user')->truncate();
        DB::table('permission_role')->truncate();
        Role::truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator'
            ],
            [
                'name' => 'manager',
                'display_name' => 'Manager'
            ],
            [
                'name' => 'editor',
                'display_name' => 'Editor'
            ],
            [
                'name' => 'user',
                'display_name' => 'Regular User'
            ],
            [
                'name' => 'vendor',
                'display_name' => 'Vendor'
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
        
        $this->command->info('Roles seeded successfully!');
    }
}