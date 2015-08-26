<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ShoppingcartProductsAddFieldAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shop_products', function (Blueprint $table) {

            $table->text('attributes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shop_products', function ($table) {

            $table->dropColumn('attributes');
        });
    }
}
