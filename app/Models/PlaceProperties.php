<?php

namespace App\Models;

use App\Http\Controllers\Api\ReviewsController;
use Illuminate\Database\Eloquent\Model;

class PlaceProperties extends Model
{
    protected $table = "place_properties";

    protected $fillable = [
        "title",
        "description",
        "price_perNight",
        "price_perWeek",
        "price_perMonth",
        "price_perYear",
        "address",
        "city_id",
        "max_people",
        "status",
        "rating_avg",
        "rating_count",
        "owner_id",
        'latitude',
        'longitude',
        'google_maps_link',
    ];
    public function owner()
    {
        return $this->belongsTo(User::class, "owner_id");
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class, "properties_id");
    }

    public function mainImage()
    {
        return $this->hasOne(PropertyImage::class, "properties_id")->where(
            "is_main",
            true,
        );
    }

    public function KostFeatures()
    {
        return $this->hasMany(KostFeature::class, "place_properties_id");
    }

    public function KostPolicies()
    {
        return $this->hasMany(KostPolicy::class, "place_properties_id");
    }

    public function PlaceFeatures()
    {
        return $this->hasMany(PlaceFeature::class, "place_properties_id");
    }

    public function PlacePolicies()
    {
        return $this->hasMany(PlacePolicy::class, "place_properties_id");
    }

    public function nearbyPlaces()
    {
        return $this->hasMany(PropertyNearbyPlace::class, "place_property_id");
    }

    public function reviews()
    {
        return $this->hasMany(Reviews::class, "place_properties_id");
    }

    public function familyMembers()
    {
        return $this->hasMany(PropertyFamilyMember::class, "place_property_id");
    }
}
