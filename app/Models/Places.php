<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\City;

class Places extends Model
{
    protected $table = "places";
    protected $fillable = ["name", "type", "city_id"];
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
