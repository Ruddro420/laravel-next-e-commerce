<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosHold extends Model
{
    protected $fillable = [
        'ref',
        'title',
        'customer_id',
        'subtotal',
        'total',
        'payload'
    ];

    protected $casts = [
        'payload' => 'array',
        'subtotal' => 'float',
        'total' => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
