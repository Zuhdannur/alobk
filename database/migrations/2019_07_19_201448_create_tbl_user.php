<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTblUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('username',30)->unique();
            $table->string('password',255);
            $table->string('role',10);
            $table->string('api_token',255)->nullable();
            $table->string('firebase_token',255)->nullable();
            $table->integer('ever_change_password')->default(0);

            $table->string('avatar')->nullable();
            $table->string('name',50)->nullable();
            $table->string('jenkel', 25)->nullable();
            $table->string('nomor_hp', 12)->nullable();
            $table->string('kelas', 25)->nullable();

            $table->integer('sekolah_id')->unsigned()->nullable();
            $table->foreign('sekolah_id')->references('id')->on('sekolah')->onDelete('cascade');

            $table->text('alamat')->nullable();
            $table->string('kota', 50)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('kota_lahir', 50)->nullable();

            $table->string('perguruan_tinggi', 50)->nullable();

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
        Schema::dropIfExists('tbl_user');
    }
}
