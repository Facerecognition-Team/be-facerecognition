<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    // Tentukan kolom mana yang bisa diisi
    protected $table = 'pegawais';
    protected $primaryKey = 'id_pegawai';
    
    protected $fillable = [
        'name', 
        'nip', 
        'no_tlp', 
        'alamat'
    ];

    // Jika Anda ingin mendefinisikan relasi dengan model Absen
    public function absensi()
    {
        return $this->hasMany(Absen::class);
    }
    public function datasets()
{
    return $this->hasMany(DataTestImages::class, 'id_pegawai', 'id_pegawai');
}

//     public function dataTestFotos()
// {
//     return $this->hasMany(DataTestImages::class);
// }
}
