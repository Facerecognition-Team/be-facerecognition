<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNamaPegawaiToDataTestPegawais extends Migration
{
    public function up()
    {
        Schema::table('data_test_pegawais', function (Blueprint $table) {
            // Menambahkan kolom nama_pegawai
            $table->string('nama_pegawai')->after('id_pegawai');
            
            // Menambahkan foreign key yang mengacu pada tabel pegawais
            $table->foreign('id_pegawai')->references('id_pegawai')->on('pegawais')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('data_test_pegawais', function (Blueprint $table) {
            // Menghapus kolom nama_pegawai dan foreign key
            $table->dropForeign(['id_pegawai']);
            $table->dropColumn('nama_pegawai');
        });
    }
}
