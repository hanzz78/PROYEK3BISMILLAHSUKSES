<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KabupatenKota extends Model
{
    use HasFactory;

    protected $table = 'kabupaten_kota';

    protected $fillable = [
        'id_provinsi',
        'nama_kabupaten_kota',
    ];

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'id_provinsi');
    }

    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class, 'id_kabupaten_kota');
    }
}