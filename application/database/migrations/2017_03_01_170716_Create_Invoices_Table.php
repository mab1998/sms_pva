<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cl_id');
            $table->string('client_name',100);
            $table->integer('created_by');
            $table->date('created')->default(date('Y-m-d'));
            $table->date('duedate')->nullable();
            $table->date('datepaid')->nullable();
            $table->string('subtotal',100)->default('0.00');
            $table->string('total',100)->default('0.00');
            $table->enum('status', ['Unpaid', 'Paid','Partially Paid','Cancelled'])->default('Unpaid');
            $table->string('pmethod',30)->nullable();
            $table->string('recurring',20);
            $table->enum('bill_created',['yes','no'])->default('no');
            $table->text('note')->nullable();
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
        Schema::dropIfExists('sys_invoices');
    }
}
