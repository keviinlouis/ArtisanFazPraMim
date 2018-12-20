<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFilesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name', 191);
            $table->string('extension', 45)->nullable();
            $table->string('path', 191);
            $table->text('url', 65535)->nullable();
            $table->text('type', 65535)->nullable();
            $table->text('description', 65535)->nullable();
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
        Schema::drop('files');
    }

}
