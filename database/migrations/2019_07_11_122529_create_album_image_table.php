<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlbumImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('album_image', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('album_id')->unsigned();
            $table->foreign('album_id')->references('id')->on('albums');
            $table->bigInteger('image_id')->unsigned();
            $table->foreign('image_id')->references('id')->on('images');
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
        Schema::dropIfExists('album_images');
    }
}
