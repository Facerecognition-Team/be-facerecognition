<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absen extends Model
{
    use HasFactory;

    // Nama tabel yang sesuai jika tidak mengikuti konvensi Laravel
    protected $table = 'absensi';
    protected $primaryKey = 'id_absensi';

    // Kolom yang dapat diisi secara massal
    protected $fillable = [
        'id_pegawai',
        'tanggal_absen',
        'waktu_masuk',
        'waktu_keluar'
    ];

    // Definisikan relasi dengan model Pegawai jika diperlukan
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai');
    }
}
