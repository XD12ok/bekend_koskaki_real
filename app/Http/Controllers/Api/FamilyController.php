<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FamilyInviteCode;
use App\Models\PropertyFamilyMember;
use App\Models\RentalBooking;
use Illuminate\Http\Request;

class FamilyController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | JOIN FAMILY
    |--------------------------------------------------------------------------
    */

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

        // cek apakah user sudah join
        $alreadyJoined = PropertyFamilyMember::where(
            "place_property_id",
            $invite->place_property_id,
        )
            ->where("user_id", auth()->id())
            ->exists();

        if ($alreadyJoined) {
            return response()->json(
                [
                    "message" => "User sudah berada di family ini",
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

    /*
    |--------------------------------------------------------------------------
    | LIST FAMILY MEMBERS
    |--------------------------------------------------------------------------
    */

    public function members($propertyId)
    {
        $authId = auth()->id();

        // cek apakah user penghuni / owner
        $hasAccess = PropertyFamilyMember::where(
            "place_property_id",
            $propertyId,
        )
            ->where("user_id", $authId)
            ->exists();

        $ownerBooking = RentalBooking::where(
            "place_property_id",
            $propertyId,
        )->first();

        $isOwner = $ownerBooking ? $ownerBooking->owner_id === $authId : false;

        if (!$hasAccess && !$isOwner) {
            return response()->json(
                [
                    "message" => "Tidak punya akses",
                ],
                403,
            );
        }

        $members = PropertyFamilyMember::with([
            "user",
            "rentalBooking.payments",
        ])
            ->where("place_property_id", $propertyId)
            ->latest()
            ->get();

        $data = $members->map(function ($member) use ($isOwner) {
            $booking = $member->rentalBooking;

            $result = [
                "id" => $member->id,

                "user" => [
                    "id" => $member->user->id,
                    "name" => $member->user->name,
                    "email" => $member->user->email,
                ],

                "joined_at" => $member->joined_at,
            ];

            // owner bisa lihat detail tambahan
            if ($isOwner && $booking) {
                $result["rental"] = [
                    "start_date" => $booking->start_date,
                    "end_date" => $booking->end_date,
                    "days_left" => now()->diffInDays($booking->end_date, false),
                    "status" => $booking->status,
                    "total_price" => $booking->total_price,
                ];
            }

            return $result;
        });

        return response()->json([
            "is_owner" => $isOwner,
            "total_members" => $members->count(),
            "data" => $data,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MEMBER DETAIL
    |--------------------------------------------------------------------------
    */

    public function memberDetail($propertyId, $userId)
    {
        $authId = auth()->id();

        $member = PropertyFamilyMember::with(["user", "rentalBooking.payments"])
            ->where("place_property_id", $propertyId)
            ->where("user_id", $userId)
            ->firstOrFail();

        $booking = $member->rentalBooking;

        $isOwner = $booking ? $booking->owner_id === $authId : false;

        $hasAccess = PropertyFamilyMember::where(
            "place_property_id",
            $propertyId,
        )
            ->where("user_id", $authId)
            ->exists();

        if (!$hasAccess && !$isOwner) {
            return response()->json(
                [
                    "message" => "Tidak punya akses",
                ],
                403,
            );
        }

        $data = [
            "user" => [
                "id" => $member->user->id,
                "name" => $member->user->name,
                "email" => $member->user->email,
            ],

            "joined_at" => $member->joined_at,
        ];

        if ($booking) {
            $data["rental"] = [
                "start_date" => $booking->start_date,
                "end_date" => $booking->end_date,
                "days_left" => now()->diffInDays($booking->end_date, false),
                "status" => $booking->status,
                "total_price" => $booking->total_price,
            ];
        }

        // khusus owner
        if ($isOwner && $booking) {
            $data["payment_history"] = $booking->payments->map(function ($p) {
                return [
                    "amount" => $p->amount,
                    "status" => $p->status,
                    "method" => $p->payment_method,
                    "verified_at" => $p->verified_at,
                ];
            });

            $data["owner_view"] = true;
        }

        return response()->json([
            "data" => $data,
        ]);
    }

    public function leave($propertyId)
    {
        $authId = auth()->id();

        $member = PropertyFamilyMember::where("place_property_id", $propertyId)
            ->where("user_id", $authId)
            ->first();

        if (!$member) {
            return response()->json(
                [
                    "message" => "Kamu bukan member family ini",
                ],
                404,
            );
        }

        // owner utama tidak boleh leave
        $booking = $member->rentalBooking;

        if ($booking && $booking->user_id == $authId) {
            return response()->json(
                [
                    "message" => "Penghuni utama tidak bisa leave",
                ],
                422,
            );
        }

        $member->delete();

        return response()->json([
            "message" => "Berhasil keluar dari family",
        ]);
    }
    public function kick($propertyId, $userId)
    {
        $authId = auth()->id();

        $booking = RentalBooking::where(
            "place_property_id",
            $propertyId,
        )->first();

        if (!$booking) {
            return response()->json(
                [
                    "message" => "Rental booking tidak ditemukan",
                ],
                404,
            );
        }

        // hanya owner
        if ($booking->owner_id !== $authId) {
            return response()->json(
                [
                    "message" => "Hanya owner yang bisa kick member",
                ],
                403,
            );
        }

        $member = PropertyFamilyMember::where("place_property_id", $propertyId)
            ->where("user_id", $userId)
            ->first();

        if (!$member) {
            return response()->json(
                [
                    "message" => "Member tidak ditemukan",
                ],
                404,
            );
        }

        // tidak boleh kick penghuni utama
        if ($booking->user_id == $userId) {
            return response()->json(
                [
                    "message" => "Penghuni utama tidak bisa di-kick",
                ],
                422,
            );
        }

        $member->delete();

        return response()->json([
            "message" => "Member berhasil di-kick",
        ]);
    }
}
