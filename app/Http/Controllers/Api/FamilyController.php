<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\FamilyInviteCode;
use App\Models\PropertyFamilyMember;
use Illuminate\Http\Request;
class FamilyController extends Controller
{
    public function join(Request $request)
    {
        $request->validate([
            "code" => "required",
        ]);
        $invite = FamilyInviteCode::where("code", $request->code)
            ->where("is_used", false)
            ->firstOrFail();
        if ($invite->expired_at && now()->gt($invite->expired_at)) {
            return response()->json(
                [
                    "message" => "Kode sudah expired",
                ],
                422,
            );
        }
        PropertyFamilyMember::create([
            "place_property_id" => $invite->place_property_id,
            "user_id" => auth()->id(),
            "rental_booking_id" => $invite->rental_booking_id,
            "joined_at" => now(),
        ]);
        $invite->update([
            "is_used" => true,
            "used_at" => now(),
        ]);
        $invite->rentalBooking->update([
            "family_joined" => true,
        ]);
        return response()->json([
            "message" => "Berhasil masuk family kost",
        ]);
    }
}
