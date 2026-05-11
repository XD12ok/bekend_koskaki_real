<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlaceProperties;
use App\Models\Reviews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewsController extends Controller
{
    // =====================
    // GET REVIEW BY PROPERTY
    // =====================
    public function index($placeId)
    {
        $reviews = Reviews::with("user")
            ->where("place_properties_id", $placeId)
            ->latest()
            ->get();

        return response()->json([
            "average_rating" => round(
                optional($reviews)->avg("rating") ?? 0,
                2,
            ),
            "total_reviews" => $reviews->count(),
            "data" => $reviews,
        ]);
    }

    // =====================
    // CREATE / UPDATE REVIEW
    // =====================
    public function store(Request $request, $placeId)
    {
        return DB::transaction(function () use ($request, $placeId) {
            $request->validate([
                "rating" => "required|integer|min:1|max:5",
                "comment" => "nullable|string|max:1000",
            ]);

            $user = $request->user();
            $place = PlaceProperties::findOrFail($placeId);

            $existingReview = Reviews::where("user_id", $user->id)
                ->where("place_properties_id", $placeId)
                ->first();

            // =====================
            // UPDATE REVIEW
            // =====================
            if ($existingReview) {
                $oldRating = $existingReview->rating;
                $newRating = $request->rating;

                $existingReview->update([
                    "rating" => $newRating,
                    "comment" => $request->comment,
                ]);

                if ($place->rating_count > 0) {
                    $newAvg =
                        ($place->rating_avg * $place->rating_count -
                            $oldRating +
                            $newRating) /
                        $place->rating_count;
                } else {
                    $newAvg = 0;
                }

                $place->update([
                    "rating_avg" => round($newAvg, 2),
                ]);

                return response()->json([
                    "message" => "Review updated",
                    "data" => $existingReview,
                ]);
            }

            // =====================
            // CREATE REVIEW
            // =====================
            $review = Reviews::create([
                "user_id" => $user->id,
                "place_properties_id" => $placeId,
                "rating" => $request->rating,
                "comment" => $request->comment,
            ]);

            $newCount = $place->rating_count + 1;

            $newAvg =
                ($place->rating_avg * $place->rating_count + $request->rating) /
                $newCount;

            $place->update([
                "rating_avg" => round($newAvg, 2),
                "rating_count" => $newCount,
            ]);

            return response()->json([
                "message" => "Review created",
                "data" => $review,
            ]);
        });
    }

    // =====================
    // DELETE REVIEW
    // =====================
    public function destroy($placeId, Request $request)
    {
        return DB::transaction(function () use ($placeId, $request) {
            $user = $request->user();

            $review = Reviews::where("user_id", $user->id)
                ->where("place_properties_id", $placeId)
                ->firstOrFail();

            $place = PlaceProperties::findOrFail($placeId);

            $oldRating = $review->rating;

            $review->delete();

            $newCount = $place->rating_count - 1;

            if ($newCount > 0) {
                $newAvg =
                    ($place->rating_avg * $place->rating_count - $oldRating) /
                    $newCount;
            } else {
                $newAvg = 0;
            }

            $place->update([
                "rating_avg" => round($newAvg, 2),
                "rating_count" => $newCount,
            ]);

            return response()->json([
                "message" => "Review deleted",
            ]);
        });
    }
}
