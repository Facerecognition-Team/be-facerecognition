<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Pegawai; // Model Pegawai yang berisi data test
use App\Models\Absen;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;

class FaceRecognitionController extends Controller
{
    public function prosesAbsen(Request $request)
    {
        try {
            // Validasi request
            if (!$request->hasFile('foto')) {
                return response()->json(['success' => false, 'message' => 'Foto tidak terdeteksi'], 400);
            }
    
            $uploadedPhoto = $request->file('foto');
            $uploadedPath = $uploadedPhoto->store('temp'); // Simpan foto sementara
            $uploadedImagePath = storage_path('app/' . $uploadedPath);
    
            // Ambil semua pegawai beserta data foto mereka
            $pegawais = Pegawai::join('data_test_pegawais', 'pegawais.id_pegawai', '=', 'data_test_pegawais.id_pegawai')->get();
    
            if ($pegawais->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data pegawai tidak tersedia',
                    'data' => [],
                ], 404); // Gunakan status 404 untuk data tidak ditemukan
            }
    
            $bestMatch = null;
            $bestMatchScore = 0;
    
            // Proses untuk setiap pegawai dan foto data testnya
            foreach ($pegawais as $pegawai) {
                $testImagePath = public_path($pegawai->url_image);  // Lokasi foto di public/image
    
                // Proses membaca dan resizing kedua gambar menggunakan GD
                $uploadedImage = $this->resizeImage($uploadedImagePath);
                $testImage = $this->resizeImage($testImagePath);
    
                // Konversi kedua gambar menjadi hash untuk pencocokan
                $uploadedHash = $this->getImageHash($uploadedImage);
                $testHash = $this->getImageHash($testImage);
    
                // Hitung persentase kesamaan
                similar_text($uploadedHash, $testHash, $percentage);
    
                // Simpan kecocokan terbaik
                if ($percentage > $bestMatchScore) {
                    $bestMatchScore = $percentage;
                    $bestMatch = $pegawai;
                }
            }
    
            // Hapus file sementara
            Storage::delete($uploadedPath);
    
            // Tentukan apakah absen masuk atau keluar berdasarkan waktu
            $currentHour = Carbon::now()->hour;
            $absenType = ($currentHour >= 16 || $currentHour < 8) ? 'keluar' : 'masuk';
    
            // Simpan data absensi ke dalam tabel absensi
            $absensi = Absen::where('id_pegawai', $bestMatch->id_pegawai ?? null)
                ->where('tanggal_absen', Carbon::now()->toDateString())
                ->first();
    
            if ($absenType === 'masuk') {
                if (!$absensi) {
                    $absensi = new Absen();
                    $absensi->id_pegawai = $bestMatch ? $bestMatch->id_pegawai : null;
                    $absensi->tanggal_absen = Carbon::now()->toDateString();
                    $absensi->waktu_masuk = Carbon::now();
                    $absensi->waktu_keluar = '00:00:00';
                    $absensi->save();
                }
            } else if ($absenType === 'keluar') {
                if ($absensi) {
                    $absensi->waktu_keluar = Carbon::now();
                    $absensi->save();
                } else {
                    $absensi = new Absen();
                    $absensi->id_pegawai = $bestMatch ? $bestMatch->id_pegawai : null;
                    $absensi->tanggal_absen = Carbon::now()->toDateString();
                    $absensi->waktu_masuk = '00:00:00';
                    $absensi->waktu_keluar = Carbon::now();
                    $absensi->save();
                }
            }
    
            // Verifikasi apakah ada kecocokan yang cukup baik
            if ($bestMatch && $bestMatchScore > 50) {
                return response()->json([
                    'success' => true,
                    'message' => 'Absen berhasil (' . ucfirst($absenType) . ')',
                    'pegawai' => $bestMatch,
                    'match_score' => $bestMatchScore,
                ]);
            }
    
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada kecocokan' . $bestMatchScore . ',' . $bestMatch,
            ], 404);
    
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    /**
     * Resize image using GD library
     */
    private function resizeImage($filePath, $width = 300, $height = 300)
    {
        $originalImage = imagecreatefromjpeg($filePath);
        $resizedImage = imagescale($originalImage, $width, $height);
        imagedestroy($originalImage); // Hapus resource asli
        return $resizedImage;
    }

    /**
     * Generate hash for image
     */
    private function getImageHash($image)
    {
        ob_start();
        imagejpeg($image);
        $imageData = ob_get_clean();
        imagedestroy($image); // Hapus resource gambar
        return md5($imageData); // Menghasilkan hash MD5
    }

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
    public function logAbsen(Request $request)
    {
        // Validasi data absensi
        $request->validate([
            'id_pegawai' => 'required|exists:pegawais,id_pegawai',
            'tanggal_absen' => 'required|date',
            'waktu_masuk' => 'required|date_format:H:i:s',
            'waktu_keluar' => 'nullable|date_format:H:i:s',
        ]);
    
        // Konversi waktu masuk dan keluar ke waktu server (WIB)
        $waktuMasuk = Carbon::createFromFormat('H:i:s', $request->input('waktu_masuk'), 'Asia/Jakarta')->toTimeString();
        $waktuKeluar = $request->has('waktu_keluar')
            ? Carbon::createFromFormat('H:i:s', $request->input('waktu_keluar'), 'Asia/Jakarta')->toTimeString()
            : null;
    
        // Buat atau update log absensi
        $absen = Absen::updateOrCreate(
            [
                'id_pegawai' => $request->input('id_pegawai'),
                'tanggal_absen' => $request->input('tanggal_absen'),
            ],
            [
                'waktu_masuk' => $waktuMasuk,
                'waktu_keluar' => $waktuKeluar,
            ]
        );
    
        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil dicatat',
            'data' => $absen,
        ]);
    }
    
    // Fungsi untuk mengambil log absensi
    public function getLogAbsen(Request $request)
    {
        // Validasi filter jika diperlukan
        $request->validate([
            'tanggal' => 'nullable|date',
            'id_pegawai' => 'nullable|exists:pegawais,id_pegawai',
        ]);
    
        // Query log absensi berdasarkan filter
        $query = Absen::query();
    
        if ($request->has('tanggal')) {
            $query->where('tanggal_absen', $request->input('tanggal'));
        }
    
        if ($request->has('id_pegawai')) {
            $query->where('id_pegawai', $request->input('id_pegawai'));
        }
    
        // Muat relasi pegawai
        $logAbsensi = $query->with('pegawai')->get();
    
        return response()->json([
            'success' => true,
            'message' => 'Log absensi berhasil diambil',
            'data' => $logAbsensi,
        ]);
    }

    
}
