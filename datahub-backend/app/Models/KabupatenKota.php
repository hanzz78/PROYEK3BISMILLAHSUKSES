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

    /**
     * Get the provinsi that owns this kabupaten/kota.
     */
    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'id_provinsi');
    }

    /**
     * Get all mahasiswa from this kabupaten/kota.
     */
    public function mahasiswas()
    {
        return $this->hasMany(Mahasiswa::class, 'id_kabupaten_kota');
    }
}
