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
        Schema::table('orders', function (Blueprint $table) {

            // Coupon
            $table->foreignId('coupon_id')
                  ->nullable()
                  ->constrained('coupons')
                  ->nullOnDelete();

            $table->string('coupon_code')->nullable();
            $table->decimal('coupon_discount', 12, 2)->default(0);

            // Tax
            $table->foreignId('tax_rate_id')
                  ->nullable()
                  ->constrained('tax_rates')
                  ->nullOnDelete();

            $table->decimal('tax_amount', 12, 2)->default(0);

        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {

            $table->dropConstrainedForeignId('coupon_id');
            $table->dropConstrainedForeignId('tax_rate_id');

            $table->dropColumn([
                'coupon_code',
                'coupon_discount',
                'tax_amount'
            ]);
        });
    }
};
