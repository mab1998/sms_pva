<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntCountryCodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_int_country_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('country_name',30)->nullable();
            $table->string('iso_code',20)->nullable();
            $table->string('country_code',5)->nullable();
            $table->decimal('plain_tariff',5,2)->default(1.00);
            $table->decimal('voice_tariff',5,2)->default(1.00);
            $table->decimal('mms_tariff',5,2)->default(1.00);
            $table->enum('active',['1','0'])->default(1);
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
        Schema::dropIfExists('sys_int_country_codes');
    }
}
