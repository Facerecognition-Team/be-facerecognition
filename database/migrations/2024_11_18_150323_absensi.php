<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id("id_absensi");
            $table->unsignedBigInteger('id_pegawai'); // Nama pegawai
            $table->date('tanggal_absen');// Kolom untuk menyimpan path foto pegawai
            $table->time('waktu_masuk');// Kolom untuk menyimpan path foto pegawai
            $table->time('waktu_keluar');// Kolom untuk menyimpan path foto pegawai
            $table->timestamps(); // Kolom created_at dan updated_at
        
            $table->foreign('id_pegawai')->references('id_pegawai')->on('pegawais');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
