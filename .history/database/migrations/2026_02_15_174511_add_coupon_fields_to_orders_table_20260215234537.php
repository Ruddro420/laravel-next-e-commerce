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
            $table->foreignId('coupon_id')->nullable()->after('status')
                  ->constrained('coupons')->nullOnDelete();

            $table->string('coupon_code')->nullable()->after('coupon_id');
            $table->decimal('coupon_discount', 12, 2)->default(0)->after('coupon_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // drop FK first, then columns
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn(['coupon_code','coupon_discount']);
        });
    }
};
