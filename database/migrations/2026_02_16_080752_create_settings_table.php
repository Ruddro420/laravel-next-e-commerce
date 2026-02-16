<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            // Software / Brand
            $table->string('software_name', 160)->default('ShopPulse');
            $table->string('software_tagline', 190)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->string('favicon_path', 255)->nullable();

            // Store / Company
            $table->string('store_name', 190)->nullable();
            $table->string('store_email', 190)->nullable();
            $table->string('store_phone', 60)->nullable();
            $table->string('support_email', 190)->nullable();
            $table->string('website', 190)->nullable();

            // Address
            $table->string('address_line1', 190)->nullable();
            $table->string('address_line2', 190)->nullable();
            $table->string('city', 120)->nullable();
            $table->string('state', 120)->nullable();
            $table->string('postal_code', 40)->nullable();
            $table->string('country', 120)->nullable();

            // Currency & Locale
            $table->string('currency_code', 10)->default('BDT');
            $table->string('currency_symbol', 10)->default('৳');
            $table->enum('currency_position', ['before', 'after'])->default('before');
            $table->string('timezone', 64)->default('Asia/Dhaka');
            $table->string('date_format', 40)->default('d M, Y');
            $table->string('time_format', 40)->default('h:i A');

            // Order / Invoice
            $table->string('invoice_prefix', 30)->default('INV-');
            $table->string('order_prefix', 30)->default('ORD-');
            $table->boolean('invoice_show_logo')->default(true);

            // Tax & Shipping defaults
            $table->boolean('tax_enabled')->default(true);
            $table->decimal('default_shipping', 12, 2)->default(0);

            // Stock
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->boolean('stock_alert_enabled')->default(true);

            // Footer / Print
            $table->text('invoice_footer_note')->nullable();
            $table->text('pos_receipt_footer')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
