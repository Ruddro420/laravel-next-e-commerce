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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            // optional: link to order
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();

            $table->enum('type', ['in', 'out', 'adjust']);   // IN / OUT / ADJUST
            $table->integer('qty');                        // movement qty (positive)
            $table->integer('before_stock')->default(0);
            $table->integer('after_stock')->default(0);

            $table->string('reason', 190)->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['product_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
