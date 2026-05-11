<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PropertyNearbyPlace;
use Illuminate\Http\Request;

class PropertyNearbyPlaceController extends Controller
{
    // =====================
    // GET ALL
    // =====================
    public function index($propertyId)
    {
        $data = PropertyNearbyPlace::with("place")
            ->where("place_property_id", $propertyId)
            ->get();

        return response()->json([
            "data" => $data,
        ]);
    }

    // =====================
    // STORE (multi input)
    // =====================
    public function store(Request $request, $propertyId)
    {
        $validated = $request->validate([
            "places" => "required|array",
            "places.*.place_id" => "required|exists:places,id",
            "places.*.distance" => "required|numeric|min:0",
        ]);

        $inserted = [];

        foreach ($validated["places"] as $item) {
            $data = PropertyNearbyPlace::updateOrCreate(
                [
                    "place_property_id" => $propertyId,
                    "place_id" => $item["place_id"],
                ],
                [
                    "distance" => $item["distance"],
                ],
            );

            $inserted[] = $data;
        }

        return response()->json(
            [
                "message" => "Nearby places saved",
                "data" => $inserted,
            ],
            201,
        );
    }

    // =====================
    // UPDATE (SYNC)
    // =====================
    public function update(Request $request, $propertyId)
    {
        $validated = $request->validate([
            "places" => "required|array",
            "places.*.place_id" => "required|exists:places,id",
            "places.*.distance" => "required|numeric|min:0",
        ]);

        $newIds = collect($validated["places"])->pluck("place_id");

        // DELETE yang tidak ada di request
        PropertyNearbyPlace::where("place_property_id", $propertyId)
            ->whereNotIn("place_id", $newIds)
            ->delete();

        $result = [];

        foreach ($validated["places"] as $item) {
            $data = PropertyNearbyPlace::updateOrCreate(
                [
                    "place_property_id" => $propertyId,
                    "place_id" => $item["place_id"],
                ],
                [
                    "distance" => $item["distance"],
                ],
            );

            $result[] = $data;
        }

        return response()->json([
            "message" => "Nearby places updated",
            "data" => $result,
        ]);
    }

    // =====================
    // DELETE ALL
    // =====================
    public function destroyAll($propertyId)
    {
        PropertyNearbyPlace::where("place_property_id", $propertyId)->delete();

        return response()->json([
            "message" => "All nearby places deleted",
        ]);
    }
}
