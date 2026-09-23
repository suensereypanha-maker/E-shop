<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockInsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_ins', function (Blueprint $table) {
            $table->id();

            $table->string('reference_no')->unique();
            $table->string('supplier_invoice_no')->nullable();

            $table->foreignId('supplier_id')->nullable()
                ->constrained('suppliers')
                ->nullOnDelete();

            $table->date('received_date');
            $table->enum('status', ['draft', 'received', 'cancelled'])->default('received');

            $table->integer('total_quantity')->default(0);
            $table->decimal('total_cost', 12, 2)->default(0.00);
            $table->text('note')->nullable();

            $table->foreignId('created_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('deleted_by')->nullable()
                ->constrained('users')
                ->nullOnDelete();

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
        Schema::dropIfExists('stock_ins');
    }
}
