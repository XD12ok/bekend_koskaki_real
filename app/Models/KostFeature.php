<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KostFeature extends Model
{
    protected $table = "kost_features";
    protected $fillable = ["feature", "place_properties_id"];

    public function placeProperty()
    {
        return $this->belongsTo(PlaceProperties::class, "place_properties_id");
    }
}
