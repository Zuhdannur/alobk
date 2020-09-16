<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->integer('condition_wali_kelas')->default(0);
            $table->integer('condition_pembimbing')->default(0);
            $table->integer('condition_guru')->default(0);
            $table->integer('condition_orang_tua')->default(0);
            $table->integer('agree_with_rules')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('condition_wali_kelas');
            $table->dropColumn('condition_pembimbing');
            $table->dropColumn('condition_guru');
            $table->dropColumn('condition_orang_tua');
            $table->dropColumn('agree_with_rules');
        });
    }
}
