<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSenderIDManagementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_sender_id_management', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sender_id',100);
            $table->string('cl_id',100)->default(0);
            $table->enum('status',['pending','block','unblock'])->default('block');
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
        Schema::dropIfExists('sys_sender_id_management');
    }
}
