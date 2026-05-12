<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $table = "conversations";
    protected $fillable = ["user_id", "owner_id", "place_property_id"];

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function owner()
    {
        return $this->belongsTo(User::class, "owner_id");
    }

    public function property()
    {
        return $this->belongsTo(PlaceProperties::class, "place_properties_id");
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
