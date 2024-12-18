<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasFactory, HasApiTokens;

        protected $table = 'admins';  // pastikan tabel sesuai dengan nama di database
        protected $fillable = ['username', 'password'];

        protected $hidden = [
            'password', 'remember_token',
        ];

        public function user()
        {
            return $this->belongsTo(Admin::class, 'id');
        }
    }
