<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblCatatanKonseling extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catatan_konseling', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('schedule_id')->unsigned()->nullable();
            $table->foreign('schedule_id')->references('id')->on('schedule')->onDelete('set null');
            $table->string('komentar')->unsigned();
            $table->float('rating')->unsigned();
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
        Schema::dropIfExists('tbl_catatan_konseling');
    }
}
