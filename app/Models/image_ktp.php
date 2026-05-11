<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class image_ktp extends Model
{
    protected $table = "image_ktp";
    protected $fillable = ["image_url", "user_info_id"];
}
