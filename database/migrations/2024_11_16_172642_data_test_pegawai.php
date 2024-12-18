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
        Schema::create('data_test_pegawais', function (Blueprint $table) {
            $table->id("id_data_test_pegawai");
            $table->unsignedBigInteger('id_pegawai');
            $table->string('name_image'); // Nama pegawai
            $table->string('url_image');// Kolom untuk menyimpan path foto pegawai
            $table->string('size_image');// Kolom untuk menyimpan path foto pegawai
            $table->timestamps(); // Kolom created_at dan updated_at
        
            $table->foreign('id_pegawai')->references('id_pegawai')->on('pegawais');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_test_pegawais');
    }
};
