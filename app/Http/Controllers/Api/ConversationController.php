<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Conversation;

class ConversationController extends Controller
{
    /*
       |--------------------------------------------------------------------------
       | GET /api/conversations
       |--------------------------------------------------------------------------
       */

    public function index()
    {
        $user = Auth::user();

        $conversations = Conversation::with([
            "user",
            "owner",
            "property",
            "messages.sender",
        ])
            ->where("user_id", $user->id)
            ->orWhere("owner_id", $user->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            "success" => true,
            "data" => $conversations,
        ]);
    }

    /*
       |--------------------------------------------------------------------------
       | POST /api/conversations
       |--------------------------------------------------------------------------
       */

    public function store(Request $request)
    {
        $validated = $request->validate([
            "owner_id" => "required|exists:users,id",
            "place_property_id" => "required|exists:place_properties,id",
        ]);

        $user = Auth::user();

        // cek apakah conversation sudah ada
        $conversation = Conversation::where([
            "user_id" => $user->id,
            "owner_id" => $validated["owner_id"],
            "place_property_id" => $validated["place_property_id"],
        ])->first();

        // kalau belum ada, buat baru
        if (!$conversation) {
            $conversation = Conversation::create([
                "user_id" => $user->id,
                "owner_id" => $validated["owner_id"],
                "place_property_id" => $validated["place_property_id"],
            ]);
        }

        return response()->json([
            "success" => true,
            "data" => $conversation,
        ]);
    }

    /*
       |--------------------------------------------------------------------------
       | GET /api/conversations/{id}
       |--------------------------------------------------------------------------
       */

    public function show($id)
    {
        $user = Auth::user();

        $conversation = Conversation::with([
            "user",
            "owner",
            "property",
            "messages.sender",
        ])->find($id);

        if (!$conversation) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Conversation tidak ditemukan",
                ],
                404,
            );
        }

        // keamanan
        if (
            $conversation->user_id !== $user->id &&
            $conversation->owner_id !== $user->id
        ) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Tidak punya akses",
                ],
                403,
            );
        }

        return response()->json([
            "success" => true,
            "data" => $conversation,
        ]);
    }
}
