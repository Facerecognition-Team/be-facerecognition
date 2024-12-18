<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataTestImages extends Model
{
    use HasFactory;
    protected $table = 'data_test_pegawais';
    protected $primaryKey = 'id_data_test_pegawai';
    
    protected $fillable = [
        'id_pegawai',
        'name_image',
        'url_image',
        'size_image'
    ];

    // public function pegawai()
    // {
    //     return $this->belongsTo(Pegawai::class);
    // }
}
