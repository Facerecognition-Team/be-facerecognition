<?php
namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\DataTestImages; // Pastikan model ini ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PegawaiController extends Controller
{
    // Fungsi untuk menambahkan data pegawai beserta foto multi
    public function store(Request $request)
    {
        // Validasi data yang diterima
        $request->validate([
            'employee-name' => 'required|string|max:255',
            'employee-nip' => 'required|string|max:20|unique:pegawais,nip',  // Validasi NIP
            'employee-phone' => 'required|string|max:15',  // Validasi no telp
            'employee-address' => 'required|string|max:255', // Validasi alamat
            'employee-photos.*' => 'required|image|mimes:jpg,jpeg,png', // Maksimal ukuran foto 2MB, mendukung banyak foto
        ]);
    
        // Buat record baru di tabel pegawai
        $pegawai = Pegawai::create([
            'name' => $request->input('employee-name'),
            'nip' => $request->input('employee-nip'),
            'no_tlp' => $request->input('employee-phone'),
            'alamat' => $request->input('employee-address'),
        ]);
    
        // Simpan foto-foto ke storage dan simpan pathnya ke tabel data_test_foto
        $photos = $request->file('employee-photos');  // Mendapatkan file foto yang diupload
        foreach ($photos as $photo) {
            // Mendapatkan nama file, URL dan ukuran foto
            $name_image = $photo->getClientOriginalName();
            $size_image = $photo->getSize();
            // Simpan file di public/image/pegawai_foto folder
            $url_image = $photo->move(public_path('storage/images/pegawai_foto'), $name_image);
            if (!$url_image) {
                return response()->json(['success' => false, 'message' => 'Gagal menyimpan foto.'], 500);
            }// Simpan setiap foto ke storage

            // Simpan data foto ke tabel data_test_foto
            DataTestImages::create([
                'id_pegawai' => $pegawai->id_pegawai,  // Relasikan foto dengan pegawai
                'name_image' => $name_image,  // Nama file foto
                'url_image' => 'storage/images/pegawai_foto/' . $name_image, // Path file foto yang disimpan
                'size_image' => $size_image,  // Ukuran file foto dalam byte
            ]);
        }

        // Kembalikan response JSON
        return response()->json([
            'success' => true,
            'employee' => $pegawai,
        ]);
    }

    public function add_pegawai(Request $request)
{
    // Validasi data yang diterima, menghilangkan validasi untuk employee-photos
    $request->validate([
        'employee-name' => 'required|string|max:255',
        'employee-nip' => 'required|string|max:20|unique:pegawais,nip',  // Validasi NIP
        'employee-phone' => 'required|string|max:15',  // Validasi no telp
        'employee-address' => 'required|string|max:255', // Validasi alamat
    ]);

    // Buat record baru di tabel pegawai
    $pegawai = Pegawai::create([
        'name' => $request->input('employee-name'),
        'nip' => $request->input('employee-nip'),
        'no_tlp' => $request->input('employee-phone'),
        'alamat' => $request->input('employee-address'),
    ]);

    // Kembalikan response JSON
    return response()->json([
        'success' => true,
        'employee' => $pegawai,
    ]);
}

    public function storeDataset(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',  // Validasi nama karyawan
            'images' => 'required|array|min:1',  // Validasi gambar, minimal 1 gambar
            'images.*' => 'mimes:jpeg,jpg,png|max:5120',  // Validasi jenis file dan ukuran maksimal
        ]);

        // Jika validasi gagal, kirim respons error
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 400);
        }

        // Mendapatkan nama karyawan
        $name = $request->input('name');

        // Cek apakah karyawan sudah terdaftar berdasarkan nama
        $pegawai = Pegawai::where('name', $name)->first();

        if (!$pegawai) {
            // Jika karyawan belum terdaftar, buat karyawan baru
            $pegawai = new Pegawai();
            $pegawai->name = $name;
            $pegawai->save();
        }

        // Folder untuk menyimpan gambar dataset wajah
        $folderPath = 'datasets/' . Str::slug($name);

        // Simpan dataset wajah (gambar)
        $savedImages = [];
        foreach ($request->file('images') as $image) {
            // Menyimpan gambar wajah ke storage
            $imagePath = $image->store($folderPath, 'public');

            // Simpan data ke tabel data_test_pegawais
            $dataTestPegawai = new DataTestImages();
            $dataTestPegawai->id_pegawai = $pegawai->id_pegawai;  // Relasikan dengan karyawan
            $dataTestPegawai->name_image = $image->getClientOriginalName();
            $dataTestPegawai->url_image = Storage::url($imagePath);
            $dataTestPegawai->size_image = $image->getSize();
            $dataTestPegawai->save();

            // Menambahkan gambar yang disimpan ke array
            $savedImages[] = [
                'file_name' => $image->getClientOriginalName(),
                'file_path' => Storage::url($imagePath),
            ];
        }

        // Mengembalikan respons sukses
        return response()->json([
            'success' => true,
            'message' => 'Dataset wajah berhasil ditambahkan.',
            'data' => $savedImages,
        ], 200);
    }

    public function update_data_pegawai(Request $request)
    {
        // Validasi data yang diterima
        $request->validate([
            'employee-name' => 'required|string|max:255',
            'employee-photos.*' => 'required|image|mimes:jpg,jpeg,png', // Maksimal ukuran foto 2MB, mendukung banyak foto
        ]);

        // Cek apakah pegawai sudah ada berdasarkan nama pegawai
        $pegawai = Pegawai::where('name', $request->input('employee-name'))->first();

        if (!$pegawai) {
            // Jika pegawai belum ada, beri respons error
            return response()->json([
                'success' => false,
                'message' => 'Pegawai dengan nama tersebut tidak ditemukan.',
            ], 404);
        }

 // Simpan foto-foto ke storage dan simpan pathnya ke tabel data_test_foto
 $photos = $request->file('employee-photos');  // Mendapatkan file foto yang diupload
 $uploadedPhotos = [];  // Menyimpan dataset foto yang berhasil diupload

 foreach ($photos as $photo) {
     // Mendapatkan nama file, URL dan ukuran foto
     $name_image = time() . '_' . $photo->getClientOriginalName();  // Menghindari penimpaan file
     $size_image = $photo->getSize();

     // Simpan file di public/image/pegawai_foto folder
     $url_image = $photo->move(public_path('storage/images/pegawai_foto'), $name_image);
     if (!$url_image) {
         return response()->json(['success' => false, 'message' => 'Gagal menyimpan foto.'], 500);
     }

     // Menambahkan data pegawai ke tabel data_test_pegawais
     $dataset = DataTestImages::create([
         'id_pegawai' => $pegawai->id_pegawai,  // Relasikan foto dengan pegawai
         'name_image' => $name_image,  // Nama file foto
         'url_image' => 'storage/images/pegawai_foto/' . $name_image, // Path file foto yang disimpan
         'size_image' => $size_image,  // Ukuran file foto dalam byte
     ]);

     $uploadedPhotos[] = $dataset;  // Menyimpan data dataset foto yang berhasil diupload
 }

        // Kembalikan respons JSON
        return response()->json([
            'success' => true,
            'message' => 'Data dataset pegawai berhasil ditambahkan.',
            'dataset' => $uploadedPhotos, // Mengembalikan data dataset yang baru dibuat
        ]);
        }
    
        // Fungsi untuk mengambil semua data pegawai
        public function get_data_pegawai_foto()
        {
            // Ambil semua pegawai beserta foto data testnya
            $pegawais = Pegawai::with('datasets')->get();
       
            // Kembalikan response JSON
            return response()->json([
                'success' => true,
                'data' => $pegawais,
            ]);
        }

        public function get_data_pegawai()
        {
            try {
                // Ambil semua data pegawai
                $employees = Pegawai::select('id_pegawai', 'name')->get();
    
                if ($employees->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada data pegawai.',
                    ], 404);
                }
    
                return response()->json([
                    'success' => true,
                    'employees' => $employees,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memuat data pegawai.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }
}
