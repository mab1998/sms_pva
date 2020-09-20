<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOperatorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_operator', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('coverage_id');
            $table->text('operator_name');
            $table->string('operator_code');
            $table->string('operator_setting');
            $table->string('plain_price',10)->default(1);
            $table->string('voice_price',10)->default(1);
            $table->string('mms_price',10)->default(1);
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
        Schema::dropIfExists('sys_operator');
    }
}
