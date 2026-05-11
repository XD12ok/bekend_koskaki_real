<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlaceFeature;
use Illuminate\Http\Request;

class PlaceFeatureController extends Controller
{
    // GET all features by place
    public function index($placeId)
    {
        return response()->json(
            PlaceFeature::where("place_properties_id", $placeId)
                ->latest()
                ->get(),
        );
    }

    // POST create
    public function store(Request $request, $placeId)
    {
        $validated = $request->validate([
            "feature" => "required|string",
        ]);

        // Pecah + bersihin + lowercase
        $features = collect(explode(",", $validated["feature"]))
            ->map(fn($item) => strtolower(trim($item)))
            ->filter() // buang kosong
            ->unique(); // buang duplikat dari input

        $inserted = [];

        foreach ($features as $feature) {
            $data = PlaceFeature::firstOrCreate([
                "place_properties_id" => $placeId,
                "feature" => $feature,
            ]);

            $inserted[] = $data;
        }

        return response()->json(
            [
                "message" => "Features processed successfully",
                "data" => $inserted,
            ],
            201,
        );
    }

    // GET detail
    public function show($placeId, $id)
    {
        $feature = PlaceFeature::where("place_properties_id", $placeId)
            ->where("id", $id)
            ->firstOrFail();

        return response()->json($feature);
    }

    // UPDATE
    public function update(Request $request, $placeId)
    {
        $validated = $request->validate([
            "feature" => "required|string",
        ]);

        // Ambil & bersihin input
        $newFeatures = collect(explode(",", $validated["feature"]))
            ->map(fn($item) => strtolower(trim($item)))
            ->filter()
            ->unique()
            ->values();

        // Ambil data lama dari DB
        $oldFeatures = PlaceFeature::where(
            "place_properties_id",
            $placeId,
        )->pluck("feature");

        // Cari yang perlu ditambahkan
        $toInsert = $newFeatures->diff($oldFeatures);

        // Cari yang perlu dihapus
        $toDelete = $oldFeatures->diff($newFeatures);

        // INSERT yang baru
        foreach ($toInsert as $feature) {
            PlaceFeature::create([
                "place_properties_id" => $placeId,
                "feature" => $feature,
            ]);
        }

        // DELETE yang tidak dipakai
        PlaceFeature::where("place_properties_id", $placeId)
            ->whereIn("feature", $toDelete)
            ->delete();

        return response()->json([
            "message" => "Features updated successfully",
            "data" => PlaceFeature::where(
                "place_properties_id",
                $placeId,
            )->get(),
        ]);
    }
    // DELETE
    public function destroy($placeId, $id)
    {
        $feature = PlaceFeature::where("place_properties_id", $placeId)
            ->where("id", $id)
            ->firstOrFail();

        $feature->delete();

        return response()->json([
            "message" => "Deleted successfully",
        ]);
    }
}
