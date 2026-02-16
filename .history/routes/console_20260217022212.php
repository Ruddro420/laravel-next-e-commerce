<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// routes/console.php or a seeder
use App\Models\Permission;

Permission::insert([
  ['name'=>'dashboard.view','group'=>'dashboard','created_at'=>now(),'updated_at'=>now()],
  ['name'=>'products.view','group'=>'products','created_at'=>now(),'updated_at'=>now()],
  ['name'=>'products.create','group'=>'products','created_at'=>now(),'updated_at'=>now()],
  ['name'=>'crm.orders','group'=>'crm','created_at'=>now(),'updated_at'=>now()],
  ['name'=>'crm.customers','group'=>'crm','created_at'=>now(),'updated_at'=>now()],
  ['name'=>'crm.coupons','group'=>'crm','created_at'=>now(),'updated_at'=>now()],
  ['name'=>'crm.taxes','group'=>'crm','created_at'=>now(),'updated_at'=>now()],
  ['name'=>'crm.payments','group'=>'crm','created_at'=>now(),'updated_at'=>now()],
  ['name'=>'pos.use','group'=>'pos','created_at'=>now(),'updated_at'=>now()],
  ['name'=>'settings.general','group'=>'settings','created_at'=>now(),'updated_at'=>now()],
  ['name'=>'settings.users','group'=>'settings','created_at'=>now(),'updated_at'=>now()],
  ['name'=>'settings.roles','group'=>'settings','created_at'=>now(),'updated_at'=>now()],
]);

