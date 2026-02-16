<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // dashboard.view, orders.create etc
            $table->string('group')->nullable(); // crm, products, pos
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Schema::dropIfExists('permissions');
    }
};
