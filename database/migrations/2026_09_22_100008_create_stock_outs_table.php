<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockOutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_outs', function (Blueprint $table) {
            $table->id();

            $table->string('reference_no')->unique();
            $table->date('date');
            $table->enum('reason', [
                'sale_dispatch',
                'damage_scrap',
                'internal_use',
                'sample',
                'return_supplier',
                'expired',
                'loss_theft',
                'other'
            ])->default('other');

            $table->string('recipient_name')->nullable();
            $table->enum('status', ['dispatched', 'cancelled'])->default('dispatched');

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
        Schema::dropIfExists('stock_outs');
    }
}
