<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Places;
use Illuminate\Http\Request;

class PlacesController extends Controller
{
    // =====================
    // GET PLACES BY CITY
    // =====================
    public function indexByCity($cityId)
    {
        $places = Places::with("city")
            ->where("city_id", $cityId)
            ->latest()
            ->get();

        return response()->json([
            "data" => $places,
        ]);
    }

    // =====================
    // STORE PLACE
    // =====================
    public function store(Request $request)
    {
        $request->validate([
            "city_id" => "required|exists:cities,id",
            "name" => "required|string|max:255",
            "type" => "required|in:station,airport,terminal,pelabuhan,halte",
        ]);

        $place = Places::create([
            "city_id" => $request->city_id,
            "name" => $request->name,
            "type" => $request->type,
        ]);

        return response()->json(
            [
                "message" => "Place created successfully",
                "data" => $place,
            ],
            201,
        );
    }

    // =====================
    // SHOW SINGLE PLACE
    // =====================
    public function show($id)
    {
        $place = Places::with("city")->findOrFail($id);

        return response()->json([
            "data" => $place,
        ]);
    }

    // =====================
    // UPDATE PLACE
    // =====================
    public function update(Request $request, $id)
    {
        $place = Places::findOrFail($id);

        $request->validate([
            "city_id" => "required|exists:cities,id",
            "name" => "required|string|max:255",
            "type" => "required|in:station,airport,terminal,pelabuhan,halte",
        ]);

        $place->update([
            "city_id" => $request->city_id,
            "name" => $request->name,
            "type" => $request->type,
        ]);

        return response()->json([
            "message" => "Place updated successfully",
            "data" => $place,
        ]);
    }

    // =====================
    // DELETE PLACE
    // =====================
    public function destroy($id)
    {
        $place = Places::findOrFail($id);

        $place->delete();

        return response()->json([
            "message" => "Place deleted successfully",
        ]);
    }
}
