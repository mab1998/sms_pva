<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_ticket_replies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tid');
            $table->integer('cl_id');
            $table->integer('admin_id')->nullable();
            $table->text('admin')->nullable();
            $table->text('name');
            $table->date('date');
            $table->text('message');
            $table->text('image')->nullable();
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
        Schema::dropIfExists('sys_ticket_replies');
    }
}
