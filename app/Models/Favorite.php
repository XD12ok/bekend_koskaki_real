<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $fillable = [
        'user_id',
        'place_properties_id'
    ];

    public function property()
    {
        return $this->belongsTo(PlaceProperties::class, 'place_properties_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
