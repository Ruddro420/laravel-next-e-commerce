<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PermissionSystemSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Clear all tables in correct order
        DB::table('permission_role')->truncate();
        DB::table('role_user')->truncate();
        DB::table('permissions')->truncate();
        DB::table('roles')->truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Create roles
        $roles = [
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'editor' => 'Editor',
            'user' => 'Regular User',
            'vendor' => 'Vendor',
        ];

        foreach ($roles as $name => $displayName) {
            Role::create([
                'name' => $name,
                'display_name' => $displayName
            ]);
        }

        // Create permissions
        $permissions = [
            // Dashboard
            ['name' => 'dashboard.view', 'group' => 'dashboard'],
            
            // Products
            ['name' => 'products.view', 'group' => 'products'],
            ['name' => 'products.create', 'group' => 'products'],
            ['name' => 'products.edit', 'group' => 'products'],
            ['name' => 'products.delete', 'group' => 'products'],
            ['name' => 'products.categories', 'group' => 'products'],
            ['name' => 'products.brands', 'group' => 'products'],
            ['name' => 'products.attributes', 'group' => 'products'],
            ['name' => 'products.reviews', 'group' => 'products'],
            
            // CRM
            ['name' => 'crm.view', 'group' => 'crm'],
            ['name' => 'crm.orders', 'group' => 'crm'],
            ['name' => 'crm.customers', 'group' => 'crm'],
            ['name' => 'crm.coupons', 'group' => 'crm'],
            ['name' => 'crm.taxes', 'group' => 'crm'],
            ['name' => 'crm.stock', 'group' => 'crm'],
            ['name' => 'crm.payments', 'group' => 'crm'],
            
            // Analytics
            ['name' => 'analytics.view', 'group' => 'analytics'],
            
            // POS
            ['name' => 'pos.use', 'group' => 'pos'],
            ['name' => 'pos.barcode', 'group' => 'pos'],
            
            // Settings
            ['name' => 'settings.view', 'group' => 'settings'],
            ['name' => 'settings.general', 'group' => 'settings'],
            ['name' => 'settings.users', 'group' => 'settings'],
            ['name' => 'settings.roles', 'group' => 'settings'],
        ];

        foreach ($permissions as $perm) {
            Permission::create($perm);
        }

        // Assign permissions to roles
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $editorRole = Role::where('name', 'editor')->first();
        $vendorRole = Role::where('name', 'vendor')->first();
        $userRole = Role::where('name', 'user')->first();

        // Admin gets all permissions
        $adminRole->permissions()->sync(Permission::all());

        // Manager gets most permissions
        $managerRole->permissions()->sync(
            Permission::whereNotIn('group', ['settings'])->pluck('id')->toArray()
        );

        // Editor gets content permissions
        $editorRole->permissions()->sync(
            Permission::whereIn('group', ['products', 'dashboard'])->pluck('id')->toArray()
        );

        // Vendor gets limited permissions
        $vendorRole->permissions()->sync(
            Permission::whereIn('name', [
                'dashboard.view',
                'products.view',
                'products.create',
                'products.edit',
                'crm.orders'
            ])->pluck('id')->toArray()
        );

        // User gets view only
        $userRole->permissions()->sync(
            Permission::whereIn('name', [
                'dashboard.view',
                'products.view'
            ])->pluck('id')->toArray()
        );

        // Create admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@shop.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
                'is_active' => true
            ]
        );
        $admin->roles()->sync([$adminRole->id]);

        $this->command->info('Permission system seeded successfully!');
    }
}