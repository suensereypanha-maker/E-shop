<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMenuTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menu_id')->index();
            $table->string('locale', 10)->index();
            $table->string('name');
            $table->timestamps();

            $table->foreign('menu_id')
                ->references('id')
                ->on('menus')
                ->onDelete('cascade');

            $table->unique(['menu_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu_translations');
    }
}
