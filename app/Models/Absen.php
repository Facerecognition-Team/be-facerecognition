<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absen extends Model
{
    use HasFactory;

    // Nama tabel yang sesuai jika tidak mengikuti konvensi Laravel
    protected $table = 'absen';

    // Kolom yang dapat diisi secara massal
    protected $fillable = [
        'pegawai_id',
        'image_path',
        'absen_time',
    ];

    // Definisikan relasi dengan model Pegawai jika diperlukan
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
}
