<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // REGISTER
    public function register(Request $request)
    {
        $request->validate([
            "name" => "required",
            "email" => "required|email:rfc,dns|unique:users",
            "password" => "required|min:6",
            "role" => "required|in:residents,owner",
        ]);

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "role" => $request->role,
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json([
            "message" =>
                "Register berhasil, silakan cek email untuk verifikasi",
        ]);
    }

    //login
    public function login(Request $request)
    {
        if (!Auth::attempt($request->only("email", "password"))) {
            return response()->json(
                [
                    "message" => "Email atau password salah",
                ],
                401,
            );
        }

        $user = Auth::user();

        // CEK EMAIL VERIFIED
        if (!$user->hasVerifiedEmail()) {
            return response()->json(
                [
                    "message" => "Email belum diverifikasi",
                ],
                403,
            );
        }

        $token = $user->createToken("api-token")->plainTextToken;

        return response()->json([
            "message" => "Login berhasil",
            "token" => $token,
            "user" => $user,
        ]);
    }
    // RESEND VERIF
    public function resend(Request $request)
    {
        $request->validate([
            "email" => "required|email|exists:users,email",
        ]);

        $user = User::where("email", $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                "message" => "Email sudah diverifikasi",
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            "message" => "Email verifikasi dikirim ulang",
        ]);
    }
    // GET USER (PROTECTED)
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    // LOGOUT
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            "message" => "Logout berhasil",
        ]);
    }

    //forgot paswwrod
    public function forgotPassword(Request $request)
    {
        $request->validate([
            "email" => "required|email|exists:users,email",
        ]);

        $status = Password::sendResetLink($request->only("email"));

        return $status === Password::RESET_LINK_SENT
            ? response()->json([
                "message" => "Link reset password dikirim ke email",
            ])
            : response()->json(["message" => "Gagal mengirim email"], 500);
    }

    //reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "token" => "required",
            "password" => "required|min:6|confirmed",
        ]);

        $status = Password::reset(
            $request->only(
                "email",
                "password",
                "password_confirmation",
                "token",
            ),
            function ($user, $password) {
                $user
                    ->forceFill([
                        "password" => Hash::make($password),
                        "remember_token" => Str::random(60),
                    ])
                    ->save();
            },
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(["message" => "Password berhasil direset"])
            : response()->json(
                ["message" => "Token tidak valid atau expired"],
                400,
            );
    }
}
