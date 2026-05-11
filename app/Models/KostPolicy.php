<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KostPolicy extends Model
{
    protected $table = "kost_policy";
    protected $fillable = ["title", "description", "place_properties_id"];
    public function placeProperty()
    {
        return $this->belongsTo(PlaceProperties::class, "place_properties_id");
    }
}
