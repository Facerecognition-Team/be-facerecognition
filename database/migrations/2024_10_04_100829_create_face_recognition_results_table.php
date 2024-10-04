<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFaceRecognitionResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('face_recognition_results', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // Nama yang diidentifikasi dari gambar, bisa null jika tidak dikenali
            $table->text('image_base64'); // Gambar dalam bentuk base64
            $table->string('status')->nullable(); // Status hasil pengenalan (misalnya: 'recognized', 'not recognized')
            $table->timestamps(); // Kolom created_at dan updated_at untuk menyimpan waktu pengiriman dan proses
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('face_recognition_results');
    }
}
