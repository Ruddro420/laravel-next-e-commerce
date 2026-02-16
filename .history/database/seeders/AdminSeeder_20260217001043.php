<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Administrator'
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@shop.com',
            'password' => Hash::make('12345678')
        ]);

        $admin->roles()->attach($adminRole->id);
    }
}
