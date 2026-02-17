<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->string('method')->nullable();          // cod/bkash/nagad/rocket
            $table->string('transaction_id')->nullable(); // optional
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('amount_due', 12, 2)->default(0);

            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->unique('order_id'); // one payment per order
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};
