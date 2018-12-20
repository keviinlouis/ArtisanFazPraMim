<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAddressTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('zip_code', 8);
            $table->text('street', 65535);
            $table->integer('number');
            $table->text('complement', 65535)->nullable();
            $table->string('neighborhood', 191)->nullable();
            $table->string('city', 191);
            $table->string('state', 2);
            $table->string('country', 10)->nullable()->default('BR');
            $table->float('latitude', 10, 0)->nullable();
            $table->float('longitude', 10, 0)->nullable();
            $table->morphs('model');
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
        Schema::drop('addresses');
    }

}
