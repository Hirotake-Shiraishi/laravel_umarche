<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shop_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('secondary_category_id')
                ->constrained();

            $table->foreignId('image1')
                ->nullable()
                ->constrained('images');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}
