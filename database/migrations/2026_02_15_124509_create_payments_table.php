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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            // IMPORTANT: Must match orders.id type
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // Payment method
            $table->enum('method', ['cod', 'bkash', 'nagad', 'rocket']);

            // Payment status
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])
                ->default('pending');

            // Amount
            $table->decimal('amount', 12, 2)->default(0);

            // Wallet fields (optional for COD)
            $table->string('trx_id')->nullable();
            $table->string('sender_number')->nullable();
            $table->string('reference')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
