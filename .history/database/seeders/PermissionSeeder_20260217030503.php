<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Clear pivot table first
        DB::table('permission_role')->truncate();
        
        // Now clear permissions
        Permission::truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

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
        $this->assignPermissionsToRoles();
        
        $this->command->info('Permissions seeded successfully!');
    }

    private function assignPermissionsToRoles(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $editorRole = Role::where('name', 'editor')->first();
        $vendorRole = Role::where('name', 'vendor')->first();
        $userRole = Role::where('name', 'user')->first();

        if ($adminRole) {
            $adminRole->permissions()->sync(Permission::all());
        }

        if ($managerRole) {
            $managerPermissions = Permission::whereNotIn('group', ['settings'])->get();
            $managerRole->permissions()->sync($managerPermissions);
        }

        if ($editorRole) {
            $editorPermissions = Permission::whereIn('group', ['products', 'dashboard'])->get();
            $editorRole->permissions()->sync($editorPermissions);
        }

        if ($vendorRole) {
            $vendorPermissions = Permission::whereIn('name', [
                'dashboard.view',
                'products.view',
                'products.create',
                'products.edit',
                'crm.orders'
            ])->get();
            $vendorRole->permissions()->sync($vendorPermissions);
        }

        if ($userRole) {
            $userPermissions = Permission::whereIn('name', [
                'dashboard.view',
                'products.view'
            ])->get();
            $userRole->permissions()->sync($userPermissions);
        }
    }
}