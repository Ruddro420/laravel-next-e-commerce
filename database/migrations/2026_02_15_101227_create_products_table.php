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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();

            $table->enum('product_type', ['simple', 'variable', 'downloadable'])->default('simple');

            $table->longText('description')->nullable();
            $table->text('short_description')->nullable();

            $table->decimal('regular_price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();

            $table->string('sku', 100)->nullable()->unique();
            $table->string('barcode', 120)->nullable();

            $table->integer('stock')->nullable(); // simple/downloadable stock
            $table->decimal('shipping_price', 12, 2)->nullable();

            // Relations (assumes you already have categories + brands tables)
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('brand_id')->nullable()->index();

            $table->string('featured_image')->nullable(); // storage path
            $table->string('download_file')->nullable();  // storage path for downloadable

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
