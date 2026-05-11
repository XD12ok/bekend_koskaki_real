<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlaceFeature extends Model
{
    protected $table = "place_features";
    protected $fillable = ["feature", "place_properties_id"];

    public function placeProperty()
    {
        return $this->belongsTo(PlaceProperties::class, "place_properties_id");
    }
}
