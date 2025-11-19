<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

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
        'id_slta',
        'id_jalur_daftar',
        'id_kabupaten_kota',
    ];

    protected $casts = [
        'tgl_lahir' => 'date',
    ];

    /**
     * Get the import record.
     */
    public function import()
    {
        return $this->belongsTo(ImportMahasiswa::class, 'import_id');
    }

    /**
     * Get the user who imported.
     */
    public function importer()
    {
        return $this->belongsTo(User::class, 'user_id_importer');
    }

    /**
     * Get the user who approved.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'user_id_approver');
    }

    /**
     * Get the SLTA.
     */
    public function slta()
    {
        return $this->belongsTo(Slta::class, 'id_slta');
    }

    /**
     * Get the jalur daftar.
     */
    public function jalurDaftar()
    {
        return $this->belongsTo(JalurDaftar::class, 'id_jalur_daftar');
    }

    /**
     * Get the kabupaten/kota.
     */
    public function kabupatenKota()
    {
        return $this->belongsTo(KabupatenKota::class, 'id_kabupaten_kota');
    }
}
