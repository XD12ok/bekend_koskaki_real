<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KostFeature;
use Illuminate\Http\Request;

class KostFeatureController extends Controller
{
    public function index($placeId)
    {
        return response()->json(
            KostFeature::where("place_properties_id", $placeId)
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
            $data = KostFeature::firstOrCreate([
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
}
