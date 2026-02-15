<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'billing_address', 'shipping_address', 'is_active'];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
