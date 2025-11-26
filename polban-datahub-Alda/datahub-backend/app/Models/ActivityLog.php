<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';
    protected $primaryKey = 'activitylog_id'; // Pastikan PK sesuai tabel

    // --- PERBAIKAN UTAMA DI SINI ---
    // 1. Aktifkan timestamps agar created_at terisi otomatis
    public $timestamps = true; 
    
    // 2. Tapi matikan updated_at karena kolomnya TIDAK ADA di database
    const UPDATED_AT = null;
    // -------------------------------

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        // 'created_at' tidak perlu masuk fillable jika otomatis
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}