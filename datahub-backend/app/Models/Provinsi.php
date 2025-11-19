<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    use HasFactory;

    protected $table = 'provinsi';

    protected $fillable = [
        'nama_provinsi',
    ];

    /**
     * Get all kabupaten/kota for this provinsi.
     */
    public function kabupatenKotas()
    {
        return $this->hasMany(KabupatenKota::class, 'id_provinsi');
    }
}
