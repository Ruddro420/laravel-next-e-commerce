<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $perms = [
            // dashboard
            ['name'=>'dashboard.view', 'group'=>'dashboard'],

            // products
            ['name'=>'products.view', 'group'=>'products'],
            ['name'=>'products.create', 'group'=>'products'],
            ['name'=>'products.edit', 'group'=>'products'],
            ['name'=>'products.delete', 'group'=>'products'],
            ['name'=>'products.categories', 'group'=>'products'],
            ['name'=>'products.brands', 'group'=>'products'],
            ['name'=>'products.attributes', 'group'=>'products'],
            ['name'=>'products.reviews', 'group'=>'products'],

            // crm
            ['name'=>'crm.orders', 'group'=>'crm'],
            ['name'=>'crm.customers', 'group'=>'crm'],
            ['name'=>'crm.coupons', 'group'=>'crm'],
            ['name'=>'crm.taxes', 'group'=>'crm'],
            ['name'=>'crm.payments', 'group'=>'crm'],
            ['name'=>'crm.stock', 'group'=>'crm'],

            // pos
            ['name'=>'pos.view', 'group'=>'pos'],
            ['name'=>'pos.barcode', 'group'=>'pos'],

            // settings
            ['name'=>'settings.general', 'group'=>'settings'],
            ['name'=>'settings.users', 'group'=>'settings'],
            ['name'=>'settings.roles', 'group'=>'settings'],
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(
                ['name' => $p['name']],
                ['group' => $p['group']]
            );
        }
    }
}
