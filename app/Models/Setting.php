<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'software_name',
        'software_tagline',
        'logo_path',
        'favicon_path',
        'store_name',
        'store_email',
        'store_phone',
        'support_email',
        'website',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'currency_code',
        'currency_symbol',
        'currency_position',
        'timezone',
        'date_format',
        'time_format',
        'invoice_prefix',
        'order_prefix',
        'invoice_show_logo',
        'tax_enabled',
        'default_shipping',
        'low_stock_threshold',
        'stock_alert_enabled',
        'invoice_footer_note',
        'pos_receipt_footer',
    ];

    protected $casts = [
        'invoice_show_logo' => 'boolean',
        'tax_enabled' => 'boolean',
        'stock_alert_enabled' => 'boolean',
        'default_shipping' => 'decimal:2',
    ];

    public static function singleton(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'software_name' => 'ShopPulse',
            'currency_code' => 'BDT',
            'currency_symbol' => '৳',
            'timezone' => 'Asia/Dhaka',
        ]);
    }
}
