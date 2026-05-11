<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CitiesController extends Controller
{
    // =====================
    // GET ALL CITIES
    // =====================
    public function index()
    {
        $cities = City::latest()->get();

        return response()->json([
            "data" => $cities,
        ]);
    }

    // =====================
    // STORE CITY
    // =====================
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|string|unique:cities,name",
        ]);

        $city = City::create([
            "name" => $request->name,
        ]);

        return response()->json(
            [
                "message" => "City created successfully",
                "data" => $city,
            ],
            201,
        );
    }

    // =====================
    // SHOW SINGLE CITY
    // =====================
    public function show($id)
    {
        $city = City::findOrFail($id);

        return response()->json([
            "data" => $city,
        ]);
    }

    // =====================
    // UPDATE CITY
    // =====================
    public function update(Request $request, $id)
    {
        $city = City::findOrFail($id);

        $request->validate([
            "name" => "required|string|unique:cities,name," . $id,
        ]);

        $city->update([
            "name" => $request->name,
        ]);

        return response()->json([
            "message" => "City updated successfully",
            "data" => $city,
        ]);
    }

    // =====================
    // DELETE CITY
    // =====================
    public function destroy($id)
    {
        $city = City::findOrFail($id);

        $city->delete();

        return response()->json([
            "message" => "City deleted successfully",
        ]);
    }
}
