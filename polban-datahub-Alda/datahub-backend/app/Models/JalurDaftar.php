<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JalurDaftar extends Model
{
    use HasFactory;

    protected $table = 'jalur_daftar';
    protected $primaryKey = 'jalur_daftar_id';
    public $timestamps = false; 

    protected $fillable = [
        'nama_jalur_daftar',
    ];
}
