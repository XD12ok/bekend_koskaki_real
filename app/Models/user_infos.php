<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class user_infos extends Model
{
    protected $table = "user_infos";
    protected $fillable = [
        "nik",
        "tanggal_lahir",
        "nama_lengkap",
        "asal_daerah",
        "umur",
        "jenis_kelamin",
        "users_id",
    ];
}
