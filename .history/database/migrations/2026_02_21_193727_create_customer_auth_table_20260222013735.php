<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_auth', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();

            // allow login by phone/email (we use customer's phone/email, but keep copies for indexing)
            $table->string('email', 190)->nullable()->index();
            $table->string('phone', 40)->nullable()->index();

            $table->string('password'); // hashed password
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_auth');
    }
};