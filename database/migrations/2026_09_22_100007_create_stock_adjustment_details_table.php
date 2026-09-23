<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockAdjustmentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_adjustment_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_adjustment_id')
                ->constrained('stock_adjustments')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('variation_id')
                ->constrained('product_variations')
                ->cascadeOnDelete();

            $table->enum('type', ['addition', 'subtraction']); // addition (+) or subtraction (-)
            $table->integer('current_stock')->default(0);      // Stock before adjustment
            $table->integer('quantity');                       // Absolute adjusted quantity (e.g. 5)
            $table->integer('final_stock')->default(0);        // Stock after adjustment
            $table->decimal('unit_cost', 12, 2)->default(0.00);
            $table->decimal('total_cost', 12, 2)->default(0.00);
            $table->string('reason')->nullable();              // Item-specific reason

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_adjustment_details');
    }
}
