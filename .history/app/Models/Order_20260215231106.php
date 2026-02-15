<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number','customer_id','status',
        'coupon_id','coupon_code','coupon_discount',
        'tax_rate_id','tax_amount',
        'subtotal','shipping','total',
        'billing_address','shipping_address','note'
    ];

    public function customer(){ return $this->belongsTo(Customer::class); }
    public function coupon(){ return $this->belongsTo(Coupon::class); }
    public function taxRate(){ return $this->belongsTo(TaxRate::class, 'tax_rate_id'); }
    public function items(){ return $this->hasMany(OrderItem::class); }
    public function payment(){ return $this->hasOne(Payment::class); }
}
