<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('did');
            $table->integer('cl_id');
            $table->integer('admin_id');
            $table->text('name');
            $table->text('email');
            $table->date('date');
            $table->text('subject');
            $table->text('message');
            $table->enum('status',['Pending','Answered','Customer Reply','Closed'])->default('Pending');
            $table->text('admin');
            $table->text('replyby')->nullable();
            $table->text('closed_by')->nullable();
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
        Schema::dropIfExists('sys_tickets');
    }
}
