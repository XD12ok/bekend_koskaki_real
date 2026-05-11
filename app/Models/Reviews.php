<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reviews extends Model
{
    protected $table = "reviews";
    protected $fillable = [
        "user_id",
        "place_properties_id",
        "rating",
        "comment",
    ];

    public function place()
    {
        return $this->belongsTo(PlaceProperties::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
