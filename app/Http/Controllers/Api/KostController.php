<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PlaceProperties;

class KostController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET /api/properties
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $properties = PlaceProperties::query()
            ->with(["city", "mainImage"])
            ->latest()
            ->paginate(10);

        return response()->json([
            "success" => true,
            "message" => "List data kos",
            "data" => $properties,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | POST /api/properties
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $user = Auth::user();

        // cek role
        if (!$user || $user->role !== "owner") {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Hanya owner yang bisa menambah kos",
                ],
                403,
            );
        }

        $validated = $request->validate([
            "title" => "required|string|max:255",
            "description" => "required|string",

            "price_perNight" => "nullable|integer|min:0",
            "price_perWeek" => "nullable|integer|min:0",
            "price_perMonth" => "nullable|integer|min:0",
            "price_perYear" => "nullable|integer|min:0",

            "address" => "required|string",

            // LOCATION
            "latitude" => "nullable|numeric|between:-90,90",
            "longitude" => "nullable|numeric|between:-180,180",

            "city_id" => "required|exists:cities,id",

            "max_people" => "required|integer|min:1",

            "status" => "required|in:active,inactive",
        ]);

        $validated["owner_id"] = $user->id;

        /*
        |--------------------------------------------------------------------------
        | AUTO GOOGLE MAPS LINK
        |--------------------------------------------------------------------------
        */

        if (isset($validated["latitude"]) && isset($validated["longitude"])) {
            $validated["google_maps_link"] =
                "https://www.google.com/maps/search/?api=1&query=" .
                $validated["latitude"] .
                "," .
                $validated["longitude"];
        }

        $property = PlaceProperties::create($validated);

        return response()->json(
            [
                "success" => true,
                "message" => "Kos berhasil dibuat",
                "data" => $property,
            ],
            201,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | GET /api/properties/{id}
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {
        $property = PlaceProperties::with([
            "owner",
            "city",
            "images",
            "KostFeatures",
            "KostPolicies",
            "placePolicies",
            "placeFeatures",
            "nearbyPlaces",
            "reviews.user",
        ])->find($id);

        if (!$property) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Data tidak ditemukan",
                ],
                404,
            );
        }

        return response()->json([
            "success" => true,
            "data" => $property,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | PUT /api/properties/{id}
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $property = PlaceProperties::find($id);

        if (!$property) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Data tidak ditemukan",
                ],
                404,
            );
        }

        // cek owner
        if ($property->owner_id !== Auth::id()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Tidak punya akses",
                ],
                403,
            );
        }

        $validated = $request->validate([
            "title" => "sometimes|string|max:255",
            "description" => "sometimes|string",

            "price_perNight" => "nullable|integer|min:0",
            "price_perWeek" => "nullable|integer|min:0",
            "price_perMonth" => "nullable|integer|min:0",
            "price_perYear" => "nullable|integer|min:0",

            "address" => "sometimes|string",

            // LOCATION
            "latitude" => "nullable|numeric|between:-90,90",
            "longitude" => "nullable|numeric|between:-180,180",

            "city_id" => "sometimes|exists:cities,id",

            "max_people" => "sometimes|integer|min:1",

            "status" => "sometimes|in:active,inactive",
        ]);

        /*
        |--------------------------------------------------------------------------
        | AUTO UPDATE GOOGLE MAPS LINK
        |--------------------------------------------------------------------------
        */

        if (isset($validated["latitude"]) && isset($validated["longitude"])) {
            $validated["google_maps_link"] =
                "https://www.google.com/maps/search/?api=1&query=" .
                $validated["latitude"] .
                "," .
                $validated["longitude"];
        }

        $property->update($validated);

        return response()->json([
            "success" => true,
            "message" => "Data berhasil diupdate",
            "data" => $property,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE /api/properties/{id}
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $property = PlaceProperties::find($id);

        if (!$property) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Data tidak ditemukan",
                ],
                404,
            );
        }

        // cek owner
        if ($property->owner_id !== Auth::id()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Tidak punya akses",
                ],
                403,
            );
        }

        $property->delete();

        return response()->json([
            "success" => true,
            "message" => "Data berhasil dihapus",
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET OWNER PROPERTIES
    |--------------------------------------------------------------------------
    */

    public function myProperties(Request $request)
    {
        $user = Auth::user();

        // cek login
        if (!$user) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Unauthenticated",
                ],
                401,
            );
        }

        // cek role owner
        if ($user->role !== "owner") {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Hanya owner yang bisa mengakses data ini",
                ],
                403,
            );
        }

        $properties = PlaceProperties::with(["city", "mainImage"])
            ->where("owner_id", $user->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            "success" => true,
            "message" => "List kos milik owner",
            "data" => $properties,
        ]);
    }
}
