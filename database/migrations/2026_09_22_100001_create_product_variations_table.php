<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductVariationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('color_id')->nullable()
                ->constrained('colors')
                ->nullOnDelete();

            $table->foreignId('size_id')->nullable()
                ->constrained('sizes')
                ->nullOnDelete();

            $table->string('sku')->nullable()->unique();
            $table->decimal('color_price', 12, 2)->default(0.00);
            $table->decimal('size_price', 12, 2)->default(0.00);

            // Stock is maintained at the variation level
            $table->integer('stock')->default(0);

            $table->string('image')->nullable();
            $table->boolean('status')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_variations');
    }
}
