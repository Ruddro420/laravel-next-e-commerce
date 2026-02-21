<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAuth extends Model
{
    protected $table = 'customer_auth';

    protected $fillable = [
        'customer_id', 'email', 'phone', 'password', 'is_active'
    ];

    protected $hidden = [
        'password',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}