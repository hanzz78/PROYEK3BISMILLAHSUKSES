<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slta extends Model
{
    use HasFactory;

    protected $table = 'slta';
    protected $primaryKey = 'slta_id';
    public $timestamps = false; 

    protected $fillable = [
        'nama_slta',
    ];
}
