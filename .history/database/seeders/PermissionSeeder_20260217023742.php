<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing permissions
        Permission::truncate();
        
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
            
            // Analytics
            ['name' => 'analytics.view', 'group' => 'analytics'],
            
            // POS
            ['name' => 'pos.use', 'group' => 'pos'],
            ['name' => 'pos.barcode', 'group' => 'pos'],
            
            // Settings
            ['name' => 'settings.view', 'group' => 'settings'],
            ['name' => 'settings.users', 'group' => 'settings'],
        ];

        foreach ($permissions as $perm) {
            Permission::create($perm);
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $editorRole = Role::where('name', 'editor')->first();
        $vendorRole = Role::where('name', 'vendor')->first();
        $userRole = Role::where('name', 'user')->first();

        // Admin gets all permissions
        if ($adminRole) {
            $adminRole->permissions()->sync(Permission::all());
        }

        // Manager gets most permissions except user management
        if ($managerRole) {
            $managerPermissions = Permission::whereNotIn('group', ['settings'])->get();
            $managerRole->permissions()->sync($managerPermissions);
        }

        // Editor gets content permissions
        if ($editorRole) {
            $editorPermissions = Permission::whereIn('group', ['products', 'dashboard'])->get();
            $editorRole->permissions()->sync($editorPermissions);
        }

        // Vendor gets limited product permissions
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

        // Regular user gets view only
        if ($userRole) {
            $userPermissions = Permission::whereIn('name', [
                'dashboard.view',
                'products.view'
            ])->get();
            $userRole->permissions()->sync($userPermissions);
        }
    }
}