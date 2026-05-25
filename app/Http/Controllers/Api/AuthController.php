<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    */

    public function register(Request $request)
    {
        $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users,email",
            "password" => "required|min:8",
            "role" => "required|in:residents,owner",
        ]);

        $user = User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "role" => $request->role,
        ]);

        try {
            $user->sendEmailVerificationNotification();

            return response()->json(
                [
                    "message" =>
                        "Register berhasil, silakan cek email untuk verifikasi",
                ],
                201,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" =>
                        "Register berhasil, tetapi gagal mengirim email verifikasi",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required",
        ]);

        if (!Auth::attempt($request->only("email", "password"))) {
            return response()->json(
                [
                    "message" => "Email atau password salah",
                ],
                401,
            );
        }

        $user = Auth::user();

        // cek email verified
        if (!$user->hasVerifiedEmail()) {
            Auth::logout();

            return response()->json(
                [
                    "message" => "Email belum diverifikasi",
                ],
                403,
            );
        }

        // hapus token lama
        $user->tokens()->delete();

        // buat token baru
        $token = $user->createToken("api-token")->plainTextToken;

        return response()->json([
            "message" => "Login berhasil",
            "token" => $token,
            "user" => $user,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RESEND EMAIL VERIFICATION
    |--------------------------------------------------------------------------
    */

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

        try {
            $user->sendEmailVerificationNotification();

            return response()->json([
                "message" => "Email verifikasi dikirim ulang",
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Gagal mengirim email verifikasi",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | GET USER
    |--------------------------------------------------------------------------
    */

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        if ($request->user()?->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            "message" => "Logout berhasil",
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FORGOT PASSWORD
    |--------------------------------------------------------------------------
    */

    public function forgotPassword(Request $request)
    {
        $request->validate([
            "email" => "required|email|exists:users,email",
        ]);

        try {
            $status = Password::sendResetLink($request->only("email"));

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    "message" =>
                        "Link reset password berhasil dikirim ke email",
                ]);
            }

            return response()->json(
                [
                    "message" => "Gagal mengirim link reset password",
                ],
                500,
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    "message" => "Terjadi kesalahan",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | RESET PASSWORD
    |--------------------------------------------------------------------------
    */

    public function resetPassword(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "token" => "required",
            "password" => "required|min:8|confirmed",
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

                // logout semua device
                $user->tokens()->delete();
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                "message" => "Password berhasil direset",
            ]);
        }

        return response()->json(
            [
                "message" => "Token tidak valid atau sudah expired",
            ],
            400,
        );
    }
}
