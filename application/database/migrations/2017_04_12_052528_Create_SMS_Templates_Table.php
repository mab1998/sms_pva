<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSMSTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_sms_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cl_id');
            $table->string('template_name',100);
            $table->string('from',100)->nullable();
            $table->text('message');
            $table->enum('global',['yes','no'])->default('no');
            $table->enum('status',['active','inactive'])->default('active');
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
        Schema::dropIfExists('sys_sms_templates');
    }
}
