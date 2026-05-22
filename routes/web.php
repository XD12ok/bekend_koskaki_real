<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Verified;
use App\Models\User;

Route::get("/email/verify/{id}/{hash}", function (
    Request $request,
    $id,
    $hash,
) {
    // cek signature
    if (!URL::hasValidSignature($request)) {
        abort(403, "Invalid or expired link");
    }

    $user = User::findOrFail($id);

    // cek hash email
    if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        abort(403, "Invalid verification hash");
    }

    // verify email
    if (!$user->hasVerifiedEmail()) {
        $user->email_verified_at = now();
        $user->save();

        event(new Verified($user));
    }

    // tampilkan halaman sukses
    return view("auth.email-verified");
})->name("verification.verify");
