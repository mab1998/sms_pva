<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_invoice_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('inv_id');
            $table->integer('cl_id');
            $table->text('item');
            $table->string('price',100)->default('0.00');
            $table->integer('qty')->default('0');
            $table->string('subtotal',100)->default('0.00');
            $table->string('tax',100)->default('0.00');
            $table->string('discount',100)->default('0.00');
            $table->string('total',100)->default('0.00');
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
        Schema::dropIfExists('sys_invoice_items');
    }
}
