<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePegawaiTabless extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id("id_pegawai");
            $table->string('name'); 
            $table->string('nip')->unique(); // NIP pegawai (kolom pengganti foto)
            $table->string('no_tlp')->nullable(); // Kolom untuk nomor telepon pegawai
            $table->string('alamat')->nullable(); // Kolom untuk nomor telepon pegawai
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
        Schema::dropIfExists('pegawais');
    }
}
