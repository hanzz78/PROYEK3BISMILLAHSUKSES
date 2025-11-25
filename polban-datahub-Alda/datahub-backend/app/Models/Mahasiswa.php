<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';
    protected $primaryKey = 'mahasiswa_id';

    protected $fillable = [
        'import_id',
        'user_id_importer',
        'user_id_approver',

        'kelas',
        'angkatan',
        'tgl_lahir',
        'jenis_kelamin',
        'agama',
        'kode_pos',

        'slta_id',
        'jalur_daftar_id',
        'wilayah_id',
    ];

    protected $casts = [
        'jenis_kelamin' => 'string', // enum
        'agama'         => 'string', // enum
        'tgl_lahir'     => 'date',
        'angkatan'      => 'integer',
    ];

    /**
     * Relasi ke ImportMahasiswa
     */
    public function import()
    {
        return $this->belongsTo(ImportMahasiswa::class, 'import_id', 'import_id');
    }

    /**
     * Relasi ke User (Importer)
     */
    public function importer()
    {
        return $this->belongsTo(User::class, 'user_id_importer', 'user_id');
    }

    /**
     * Relasi ke User (Approver)
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'user_id_approver', 'user_id');
    }

    /**
     * Relasi ke master SLTA
     */
    public function slta()
    {
        return $this->belongsTo(Slta::class, 'slta_id', 'slta_id');
    }

    /**
     * Relasi ke master Jalur Daftar
     */
    public function jalurDaftar()
    {
        return $this->belongsTo(JalurDaftar::class, 'jalur_daftar_id', 'jalur_daftar_id');
    }

    /**
     * Relasi ke master Wilayah
     */
    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id', 'wilayah_id');
    }

    /**
     * Relasi ke master provinsi
     */
    public function provinsi(): HasOneThrough
    {
        return $this->hasOneThrough(
            Provinsi::class,
            Wilayah::class,
            "wilayah_id",
            "provinsi_id",
            "wilayah_id",
            "provinsi_id",
        );
    }
}
