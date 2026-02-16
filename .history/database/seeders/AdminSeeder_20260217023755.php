<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@shop.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
                'is_active' => true
            ]
        );

        // Assign admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole && !$admin->hasRole('admin')) {
            $admin->roles()->attach($adminRole->id);
        }

        // Create a test manager
        $manager = User::updateOrCreate(
            ['email' => 'manager@shop.com'],
            [
                'name' => 'Manager',
                'password' => Hash::make('12345678'),
                'is_active' => true
            ]
        );

        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole && !$manager->hasRole('manager')) {
            $manager->roles()->attach($managerRole->id);
        }

        // Create a test regular user
        $user = User::updateOrCreate(
            ['email' => 'user@shop.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('12345678'),
                'is_active' => true
            ]
        );

        $userRole = Role::where('name', 'user')->first();
        if ($userRole && !$user->hasRole('user')) {
            $user->roles()->attach($userRole->id);
        }
    }
}