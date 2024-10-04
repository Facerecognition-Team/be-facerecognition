<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    // Tentukan kolom mana yang bisa diisi
    protected $fillable = ['name'];

    // Jika Anda ingin mendefinisikan relasi dengan model Absen
    public function absen()
    {
        return $this->hasMany(Absen::class);
    }
}
