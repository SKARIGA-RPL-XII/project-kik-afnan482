<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan'; // nama tabel sesuai migration

    protected $fillable = [
        'nama',
        'email',
        'phone',
        'alamat',
    ];
}
