<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
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
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }
}