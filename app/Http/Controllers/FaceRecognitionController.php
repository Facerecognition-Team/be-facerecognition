<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

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

         // Simpan gambar ke server dan ambil path-nya
         $imagePath = $image->store('images/absen', 'public'); // Simpan di folder public/images/absen

         if (!$imagePath) {
             return response()->json(['error' => 'Failed to save image to server'], 500);
         }
 
         // Dapatkan waktu saat ini (jam absen)
         $timestamp = Carbon::now();
 
         // Simpan data ke tabel absen (path, id pegawai, jam absen)
         try {
             DB::table('absen')->insert([
                 'pegawai_id' => $id,
                 'image_path' => $imagePath,
                 'absen_time' => $timestamp,
             ]);
         } catch (\Exception $e) {
             // Jika gagal, hapus gambar yang sudah disimpan
             Storage::delete($imagePath);
             return response()->json(['error' => 'Failed to save attendance record'], 500);
         }
 
         // Kirim gambar ke server Flask untuk pengenalan wajah
         $imageData = fopen(storage_path('app/public/' . $imagePath), 'r');
         $response = Http::attach('image', $imageData, basename($imagePath))
                         ->post('http://localhost:5000/recognize'); // Sesuaikan URL Flask server
 
         // Cek apakah respons dari Flask berhasil
         if ($response->successful()) {
             // Ambil data pegawai berdasarkan ID
             $pegawai = DB::table('pegawai')->where('id', $id)->first();
 
             if (!$pegawai) {
                 return response()->json(['error' => 'Employee not found'], 404);
             }
 
             // Kembalikan respon berupa ID pegawai, nama, dan jam absen
             return response()->json([
                 'id' => $pegawai->id,
                 'name' => $pegawai->name,
                 'absen_time' => $timestamp,
                 'recognition_result' => $response->json(),
             ]);
         } else {
             // Jika pengenalan wajah gagal, hapus data absen dan gambar
             DB::table('absen')->where('image_path', $imagePath)->delete();
             Storage::delete($imagePath);
             return response()->json(['error' => 'Failed to process the image for face recognition'], 500);
         }

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
