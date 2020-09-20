<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_ticket_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ticket_id');
            $table->integer('cl_id');
            $table->integer('admin_id')->nullable();
            $table->text('admin')->nullable();
            $table->text('file_title');
            $table->string('file_size',20);
            $table->text('file');
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
        Schema::dropIfExists('sys_ticket_files');
    }
}
