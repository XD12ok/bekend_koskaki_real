<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KostPolicy;
use Illuminate\Http\Request;

class KostPolicyController extends Controller
{
    public function index($placeId)
    {
        return response()->json(
            KostPolicy::where("place_properties_id", $placeId)->latest()->get(),
        );
    }

    // POST
    public function store(Request $request, $placeId)
    {
        $validated = $request->validate([
            "title" => "required|string|max:255",
            "description" => "nullable|string",
        ]);

        $policy = KostPolicy::create([
            "place_properties_id" => $placeId,
            "title" => $validated["title"],
            "description" => $validated["description"] ?? null,
        ]);

        return response()->json($policy, 201);
    }

    // GET detail
    public function show($placeId, $id)
    {
        $policy = KostPolicy::where("place_properties_id", $placeId)
            ->where("id", $id)
            ->firstOrFail();

        return response()->json($policy);
    }

    // UPDATE
    public function update(Request $request, $placeId, $id)
    {
        $policy = KostPolicy::where("place_properties_id", $placeId)
            ->where("id", $id)
            ->firstOrFail();

        $validated = $request->validate([
            "title" => "sometimes|string|max:255",
            "description" => "nullable|string",
        ]);

        $policy->update($validated);

        return response()->json($policy);
    }

    // DELETE
    public function destroy($placeId, $id)
    {
        $policy = KostPolicy::where("place_properties_id", $placeId)
            ->where("id", $id)
            ->firstOrFail();

        $policy->delete();

        return response()->json([
            "message" => "Deleted successfully",
        ]);
    }
}
