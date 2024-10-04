<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FaceRecognitionController extends Controller
{
    public function sendImage(Request $request)
    {
        // Gunakan 2 tabel, absen, dan pegawai

        // Validasi bahwa permintaan berisi file gambar
        $request->validate([
            'image' => 'required|image|max:10240', // Ukuran maksimal 10MB
            'id' => 'required|integer',
        ]);

        // Ambil gambar yang diunggah
        $image = $request->file('image');
        $id = $request->input('id');
        return $id;

        // Simpan gambar ke server, lalu ambil path-nya

        // Jika sukses simpan gambar ke server, dilanjut simpan ke tabel absen path, id, jam absen (pakai get time di php) ke database

        // Jika gagal simpan gambar, return error

        // Jika sukses simpan data, kembalikan respon id, nama dari tabel pegawai dan jam absen

        // Jika gagal, kembalikan pesan error dan hapus gambar dari server

        // Cek apakah respons dari Flask berhasil
        // if ($response->successful()) {
        //     return response()->json($response->json());
        // } else {
        //     return response()->json(['error' => 'Failed to process the image'], $response->status());
        // }
    }
}
