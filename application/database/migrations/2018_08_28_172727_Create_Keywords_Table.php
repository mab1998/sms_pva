<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeywordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_keywords', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->default(0);
            $table->text('title');
            $table->text('keyword_name');
            $table->text('reply_text')->nullable();
            $table->text('reply_voice')->nullable();
            $table->text('reply_mms')->nullable();
            $table->enum('status',['available','assigned','expired'])->default('available');
            $table->string('price',50)->default(0);
            $table->string('validity',10)->default(0);
            $table->date('validity_date')->nullable();
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
        Schema::dropIfExists('sys_keywords');
    }
}
