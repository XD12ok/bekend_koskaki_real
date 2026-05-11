<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Verified;
use App\Models\User;

//use Illuminate\Foundation\Auth\EmailVerificationRequest;

Route::get("/email/verify/{id}/{hash}", function (
    Request $request,
    $id,
    $hash,
) {
    // signature valid
    if (!URL::hasValidSignature($request)) {
        abort(403, "Invalid or expired link");
    }

    $user = User::findOrFail($id);

    // hash email
    if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        abort(403, "Invalid verification hash");
    }

    // verify email
    if (!$user->hasVerifiedEmail()) {
        $user->email_verified_at = now();
        $user->save();

        event(new Verified($user));
    }
    // if (!$user->hasVerifiedEmail()) {
    //     $user->markEmailAsVerified();
    // }

    return redirect("koskaki://verified");
})->name("verification.verify");
