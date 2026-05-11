<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Conversation;
use App\Models\Message;

class MessageController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GET /api/conversations/{id}/messages
    |--------------------------------------------------------------------------
    */

    public function index($id)
    {
        $user = Auth::user();

        $conversation = Conversation::find($id);

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

        $messages = Message::with("sender")
            ->where("conversation_id", $id)
            ->latest()
            ->paginate(30);

        return response()->json([
            "success" => true,
            "data" => $messages,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | POST /api/conversations/{id}/messages
    |--------------------------------------------------------------------------
    */

    public function store(Request $request, $id)
    {
        $validated = $request->validate([
            "message" => "required|string",
        ]);

        $user = Auth::user();

        $conversation = Conversation::find($id);

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

        $message = Message::create([
            "conversation_id" => $conversation->id,
            "sender_id" => $user->id,
            "message" => $validated["message"],
        ]);

        return response()->json(
            [
                "success" => true,
                "message" => "Pesan berhasil dikirim",
                "data" => $message,
            ],
            201,
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PUT /api/conversations/{id}/read
    |--------------------------------------------------------------------------
    */

    public function markAsRead($id)
    {
        $user = Auth::user();

        $conversation = Conversation::find($id);

        if (!$conversation) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Conversation tidak ditemukan",
                ],
                404,
            );
        }

        Message::where("conversation_id", $id)
            ->where("sender_id", "!=", $user->id)
            ->update([
                "is_read" => true,
            ]);

        return response()->json([
            "success" => true,
            "message" => "Pesan ditandai sudah dibaca",
        ]);
    }
}
