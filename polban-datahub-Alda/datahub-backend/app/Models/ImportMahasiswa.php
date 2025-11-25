<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportMahasiswa extends Model
{
    use HasFactory;

    protected $table = 'import_mahasiswa';
    protected $primaryKey = 'import_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'status',
        'admin_notes',
        'file_name',    
        'file_path',   
        'total_rows',
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
        'admin_notes',
    ];

    protected $casts = [
        'status'        => 'string',   // import_status_enum
        'jenis_kelamin' => 'string',   // jenis_kelamin_enum
        'agama'         => 'string',   // agama_enum
        'tgl_lahir'     => 'date',
        'angkatan'      => 'integer',
        'total_rows'    => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
