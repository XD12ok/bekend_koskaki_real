<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyNearbyPlace extends Model
{
    protected $table = "property_nearby_places";
    protected $fillable = ["distance", "place_id", "place_property_id"];

    public function place()
    {
        return $this->belongsTo(Places::class);
    }
}
