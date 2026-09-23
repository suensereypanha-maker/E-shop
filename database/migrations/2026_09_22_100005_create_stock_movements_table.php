<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->foreignId('variation_id')->nullable()
                ->constrained('product_variations')
                ->nullOnDelete();

            $table->enum('type', ['stock_in', 'stock_out', 'sale', 'return', 'adjustment', 'transfer']);
            $table->string('reference_type')->nullable(); // e.g. App\Models\Backend\StockIn
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->integer('quantity'); // Positive for IN, Negative for OUT
            $table->integer('stock_before')->default(0);
            $table->integer('stock_after')->default(0);
            $table->decimal('unit_cost', 12, 2)->nullable();

            $table->text('note')->nullable();

            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();

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
        Schema::dropIfExists('stock_movements');
    }
}
