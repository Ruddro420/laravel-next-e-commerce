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
            // drop FK first, then columns
            $table->dropConstrainedForeignId('tax_rate_id');
            $table->dropColumn(['tax_amount']);
        });
    }
};
