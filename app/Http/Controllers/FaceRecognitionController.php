<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FaceRecognitionController extends Controller
{
    public function sendImage(Request $request)
    {
        // Validasi bahwa permintaan berisi file gambar
        $request->validate([
            'image' => 'required|image|max:10240', // Ukuran maksimal 10MB
        ]);

        // Ambil gambar yang diunggah
        $image = $request->file('image');

        // Konversi gambar ke base64
        $imageBase64 = base64_encode(file_get_contents($image));

        // Kirim permintaan POST ke server Flask dengan gambar
        $response = Http::post('http://127.0.0.1:5000/recognize', [
            'image' => $imageBase64,
        ]);

        // Cek apakah respons dari Flask berhasil
        if ($response->successful()) {
            return response()->json($response->json());
        } else {
            return response()->json(['error' => 'Failed to process the image'], $response->status());
        }
    }
}
