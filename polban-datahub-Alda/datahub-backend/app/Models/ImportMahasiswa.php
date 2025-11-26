<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'import_mahasiswa';
    
    // Karena di migrasi kita pakai $table->id('import_id'), maka PK-nya import_id
    protected $primaryKey = 'import_id';

    // Izinkan mass assignment untuk kolom-kolom ini
    protected $fillable = [
        'user_id',
        'status',
        'file_name',    
        'file_path',   
        'total_rows',
        'admin_notes',
        'kelas',
        'angkatan',
        'tgl_lahir',
        'jenis_kelamin',
        'agama',
        'kode_pos',
        'nama_slta_raw',
        'nama_jalur_daftar_raw',
        'nama_wilayah_raw',
        'provinsi_raw',
    ];

    protected $casts = [
        'tgl_lahir'     => 'date',
        'angkatan'      => 'integer',
        'total_rows'    => 'integer',
    ];

    public function user()
    {
        // Sesuaikan FK users (user_id) dengan PK users (user_id)
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}