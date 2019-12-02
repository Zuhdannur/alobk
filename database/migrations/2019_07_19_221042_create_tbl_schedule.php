<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblSchedule extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedule', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('requester_id')->unsigned();
            $table->integer('consultant_id')->unsigned()->default(0);

            $table->string('title');
            $table->text('desc');
            $table->string('type_schedule');
            $table->dateTime('time')->nullable();
            $table->string('location')->nullable();

            $table->string('updated_old_time')->nullable();
            $table->string('updated_new_time')->nullable();

            /*Saat konseling tidak diterima sama sekali*/
            $table->integer('expired')->default(0);
            /*Saat konseling diterima lalu dibatalkan oleh guru/siswa */
            $table->integer('canceled')->default(0);
            $table->integer('pending')->default(1);
            $table->integer('finish')->default(0);
            /*3 tipe, 0 = pending, 1 = diterima, 2 = selesai*/
            $table->integer('active')->default(0);
            /*Saat konseling sedang berlangsung*/
            /*Pilihan 0 atau 1*/
            $table->integer('start')->default(0);

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
        Schema::dropIfExists('tbl_schedule');
    }
}
