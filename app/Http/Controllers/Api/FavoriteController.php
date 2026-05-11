<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    // =====================
    // TOGGLE FAVORITE
    // =====================
    public function toggle($placeId, Request $request)
    {
        $user = $request->user();

        $favorite = Favorite::where('user_id', $user->id)
            ->where('place_properties_id', $placeId)
            ->first();

        // kalau sudah ada → UNFAVORITE
        if ($favorite) {
            $favorite->delete();

            return response()->json([
                'message' => 'Removed from favorite',
                'is_favorite' => false
            ]);
        }

        // kalau belum → FAVORITE
        Favorite::create([
            'user_id' => $user->id,
            'place_properties_id' => $placeId
        ]);

        return response()->json([
            'message' => 'Added to favorite',
            'is_favorite' => true
        ]);
    }

    // =====================
    // GET MY FAVORITES
    // =====================
    public function index(Request $request)
    {
        $favorites = Favorite::with('property')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $favorites
        ]);
    }

    // =====================
    // CHECK FAVORITE (optional)
    // =====================
    public function check($placeId, Request $request)
    {
        $exists = Favorite::where('user_id', $request->user()->id)
            ->where('place_properties_id', $placeId)
            ->exists();

        return response()->json([
            'is_favorite' => $exists
        ]);
    }
}
