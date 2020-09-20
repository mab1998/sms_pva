<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_admins', function (Blueprint $table) {
            $table->increments('id');
            $table->text('fname');
            $table->text('lname')->nullable();
            $table->text('username');
            $table->text('password');
            $table->enum('status',['Active','Inactive'])->default('Active');
            $table->text('email')->nullable();
            $table->text('image')->nullable();
            $table->integer('roleid');
            $table->dateTime('lastlogin')->nullable();
            $table->text('pwresetkey')->nullable();
            $table->integer('pwresetexpiry')->nullable();
            $table->enum('emailnotify',['Yes','No'])->default('No');
            $table->integer('online')->default(0);
            $table->integer('menu_open')->default(0);
            $table->text('remember_token')->nullable();
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
        Schema::dropIfExists('sys_admins');
    }
}
