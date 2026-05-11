<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacePolicy extends Model
{
    protected $table = "place_policies";
    protected $fillable = ["title", "description", "place_properties_id"];
    public function placeProperty()
    {
        return $this->belongsTo(PlaceProperties::class, "place_properties_id");
    }
}
