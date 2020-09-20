<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_contact_list', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pid');
            $table->string('phone_number',20);
            $table->text('email_address',50)->nullable();
            $table->text('user_name',50)->nullable();
            $table->text('company',50)->nullable();
            $table->text('first_name',50)->nullable();
            $table->text('last_name',50)->nullable();
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
        Schema::dropIfExists('sys_contact_list');
    }
}
