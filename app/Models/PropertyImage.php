<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyImage extends Model
{
    protected $table = "property_images";
    protected $fillable = ["url", "is_main", "properties_id"];
    public $timestamps = false;

    protected $appends = ["full_image_url"];

    public function getFullImageUrlAttribute()
    {
        return asset("storage/" . $this->image_url);
    }
}
