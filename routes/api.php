<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    KostController,
    PropertyImagesController,
    CitiesController,
    PlacesController,
    ReviewsController,
    PlacePolicyController,
    PlaceFeatureController,
    PropertyNearbyPlaceController,
    FavoriteController,
    KostFeatureController,
    KostPolicyController,
    ConversationController,
    MessageController,
    BookingController,
    BookingRescheduleController,
    RentalBookingController,
    RentalPaymentController,
    FamilyController,
};

/*
|--------------------------------------------------------------------------
| TEST
|--------------------------------------------------------------------------
*/

Route::get("/test", function () {
    return response()->json([
        "message" => "API jalan",
    ]);
});

Route::post("/test", function () {
    return response()->json([
        "ok" => true,
    ]);
});

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::post("/auth/register", [AuthController::class, "register"]);
Route::post("/auth/login", [AuthController::class, "login"]);
Route::post("/auth/email/resend", [
    AuthController::class,
    "resend",
])->middleware("throttle:6,1");
Route::post("/auth/forgot-password", [AuthController::class, "forgotPassword"]);
Route::post("/auth/reset-password", [AuthController::class, "resetPassword"]);

/*
|--------------------------------------------------------------------------
| PUBLIC PROPERTIES
|--------------------------------------------------------------------------
*/

Route::get("/properties", [KostController::class, "index"]);
Route::get("/properties/{property}", [
    KostController::class,
    "show",
])->whereNumber("property");

Route::get("/properties/{property}/images", [
    PropertyImagesController::class,
    "index",
])->whereNumber("property");
Route::get("/properties/{property}/reviews", [
    ReviewsController::class,
    "index",
])->whereNumber("property");
Route::get("/properties/{property}/nearby-places", [
    PropertyNearbyPlaceController::class,
    "index",
])->whereNumber("property");
Route::get("/properties/{property}/policies", [
    PlacePolicyController::class,
    "index",
])->whereNumber("property");
Route::get("/properties/{property}/features", [
    PlaceFeatureController::class,
    "index",
])->whereNumber("property");

Route::get("/properties/place-properties/{placeId}/features", [
    KostFeatureController::class,
    "index",
])->whereNumber("placeId");

/*
|--------------------------------------------------------------------------
| CITIES
|--------------------------------------------------------------------------
*/

Route::get("/cities", [CitiesController::class, "index"]);
Route::get("/cities/{city}", [CitiesController::class, "show"])->whereNumber(
    "city",
);
Route::get("/cities/{city}/places", [
    PlacesController::class,
    "indexByCity",
])->whereNumber("city");

/*
|--------------------------------------------------------------------------
| PLACES
|--------------------------------------------------------------------------
*/

Route::get("/places/{place}", [PlacesController::class, "show"])->whereNumber(
    "place",
);
Route::get("/places/{place}/reviews", [
    ReviewsController::class,
    "index",
])->whereNumber("place");

/*
|--------------------------------------------------------------------------
| PLACE PROPERTIES
|--------------------------------------------------------------------------
*/

Route::get("/place-properties/{placeId}/policies", [
    KostPolicyController::class,
    "index",
])->whereNumber("placeId");
Route::get("/place-properties/{placeId}/policies/{id}", [
    KostPolicyController::class,
    "show",
])
    ->whereNumber("placeId")
    ->whereNumber("id");

/*
|--------------------------------------------------------------------------
| PROTECTED (SANCTUM)
|--------------------------------------------------------------------------
*/

Route::middleware("auth:sanctum")->group(function () {
    // USER
    Route::get("/user", [AuthController::class, "user"]);
    Route::post("/logout", [AuthController::class, "logout"]);

    // MY PROPERTIES
    Route::get("/my-properties", [KostController::class, "myProperties"]);

    // FAVORITES
    Route::get("/favorites", [FavoriteController::class, "index"]);

    /*
    |--------------------------------------------------------------------------
    | PROPERTIES CRUD
    |--------------------------------------------------------------------------
    */

    Route::post("/properties", [KostController::class, "store"]);

    Route::put("/properties/{property}", [
        KostController::class,
        "update",
    ])->whereNumber("property");
    Route::delete("/properties/{property}", [
        KostController::class,
        "destroy",
    ])->whereNumber("property");

    Route::post("/properties/{property}/images", [
        PropertyImagesController::class,
        "store",
    ])->whereNumber("property");
    Route::put("/properties/{property}/images/{image}", [
        PropertyImagesController::class,
        "update",
    ])
        ->whereNumber("property")
        ->whereNumber("image");

    Route::delete("/properties/{property}/images/{image}", [
        PropertyImagesController::class,
        "destroy",
    ])
        ->whereNumber("property")
        ->whereNumber("image");

    Route::patch("/properties/{property}/images/{image}/main", [
        PropertyImagesController::class,
        "setMain",
    ])
        ->whereNumber("property")
        ->whereNumber("image");

    Route::post("/properties/{property}/reviews", [
        ReviewsController::class,
        "store",
    ])->whereNumber("property");
    Route::delete("/properties/{property}/reviews", [
        ReviewsController::class,
        "destroy",
    ])->whereNumber("property");

    Route::post("/properties/{property}/nearby-places", [
        PropertyNearbyPlaceController::class,
        "store",
    ])->whereNumber("property");
    Route::put("/properties/{property}/nearby-places", [
        PropertyNearbyPlaceController::class,
        "update",
    ])->whereNumber("property");
    Route::delete("/properties/{property}/nearby-places", [
        PropertyNearbyPlaceController::class,
        "destroyAll",
    ])->whereNumber("property");

    Route::post("/properties/{property}/favorite", [
        FavoriteController::class,
        "toggle",
    ])->whereNumber("property");
    Route::get("/properties/{property}/favorite", [
        FavoriteController::class,
        "check",
    ])->whereNumber("property");

    /*
    |--------------------------------------------------------------------------
    | CITIES CRUD
    |--------------------------------------------------------------------------
    */

    Route::post("/cities", [CitiesController::class, "store"]);
    Route::put("/cities/{city}", [
        CitiesController::class,
        "update",
    ])->whereNumber("city");
    Route::delete("/cities/{city}", [
        CitiesController::class,
        "destroy",
    ])->whereNumber("city");

    /*
    |--------------------------------------------------------------------------
    | PLACES CRUD
    |--------------------------------------------------------------------------
    */

    Route::post("/places", [PlacesController::class, "store"]);
    Route::put("/places/{place}", [
        PlacesController::class,
        "update",
    ])->whereNumber("place");
    Route::delete("/places/{place}", [
        PlacesController::class,
        "destroy",
    ])->whereNumber("place");

    Route::post("/places/{place}/reviews", [
        ReviewsController::class,
        "store",
    ])->whereNumber("place");
    Route::delete("/places/{place}/reviews", [
        ReviewsController::class,
        "destroy",
    ])->whereNumber("place");

    /*
    |--------------------------------------------------------------------------
    | PROPERTY EXTRA
    |--------------------------------------------------------------------------
    */

    Route::post("/properties/{property}/policies", [
        PlacePolicyController::class,
        "store",
    ]);
    Route::put("/properties/{property}/policies/{policy}", [
        PlacePolicyController::class,
        "update",
    ])->whereNumber("policy");
    Route::delete("/properties/{property}/policies/{policy}", [
        PlacePolicyController::class,
        "destroy",
    ])->whereNumber("policy");

    Route::post("/properties/{property}/features", [
        PlaceFeatureController::class,
        "store",
    ]);
    Route::put("/properties/{property}/features", [
        PlaceFeatureController::class,
        "update",
    ]);
    Route::delete("/properties/{property}/features", [
        PlaceFeatureController::class,
        "destroyAll",
    ]);

    Route::post("/properties/{property}/place-properties/{placeId}/features", [
        KostFeatureController::class,
        "store",
    ])->whereNumber("placeId");

    Route::post("/properties/{property}/place-properties/{placeId}/policies", [
        KostPolicyController::class,
        "store",
    ])->whereNumber("placeId");

    Route::put(
        "/properties/{property}/place-properties/{placeId}/policies/{id}",
        [KostPolicyController::class, "update"],
    )
        ->whereNumber("placeId")
        ->whereNumber("id");

    Route::delete(
        "/properties/{property}/place-properties/{placeId}/policies/{id}",
        [KostPolicyController::class, "destroy"],
    )
        ->whereNumber("placeId")
        ->whereNumber("id");

    /*
    |--------------------------------------------------------------------------
    | BOOKINGS
    |--------------------------------------------------------------------------
    */

    Route::get("/bookings", [BookingController::class, "index"]);
    Route::get("/bookings/{id}", [
        BookingController::class,
        "show",
    ])->whereNumber("id");
    Route::post("/bookings", [BookingController::class, "store"]);

    Route::post("/bookings/{id}/accept", [
        BookingController::class,
        "accept",
    ])->whereNumber("id");
    Route::post("/bookings/{id}/reject", [
        BookingController::class,
        "reject",
    ])->whereNumber("id");
    Route::post("/bookings/{id}/cancel", [
        BookingController::class,
        "cancel",
    ])->whereNumber("id");
    Route::post("/bookings/{id}/complete", [
        BookingController::class,
        "complete",
    ])->whereNumber("id");

    /*
    |--------------------------------------------------------------------------
    | RESCHEDULE
    |--------------------------------------------------------------------------
    */

    Route::get("/booking-reschedules", [
        BookingRescheduleController::class,
        "index",
    ]);
    Route::get("/booking-reschedules/{id}", [
        BookingRescheduleController::class,
        "show",
    ])->whereNumber("id");

    Route::post("/booking-reschedules", [
        BookingRescheduleController::class,
        "store",
    ]);
    Route::post("/booking-reschedules/{id}/approve", [
        BookingRescheduleController::class,
        "approve",
    ])->whereNumber("id");
    Route::post("/booking-reschedules/{id}/reject", [
        BookingRescheduleController::class,
        "reject",
    ])->whereNumber("id");
    Route::post("/booking-reschedules/{id}/cancel", [
        BookingRescheduleController::class,
        "cancel",
    ])->whereNumber("id");

    /*
    |--------------------------------------------------------------------------
    | RENTAL BOOKINGS
    |--------------------------------------------------------------------------
    */
    Route::get("/rental-bookings", [RentalBookingController::class, "index"]);
    Route::get("/rental-bookings/{id}", [
        RentalBookingController::class,
        "show",
    ])->whereNumber("id");
    Route::post("/rental-bookings", [RentalBookingController::class, "store"]);
    Route::post("/rental-bookings/{id}/cancel", [
        RentalBookingController::class,
        "cancel",
    ])->whereNumber("id");
    /*
    |--------------------------------------------------------------------------
    | RENTAL PAYMENTS
    |--------------------------------------------------------------------------
    */
    Route::post("/rental-bookings/{id}/upload-payment", [
        RentalPaymentController::class,
        "uploadProof",
    ])->whereNumber("id");
    Route::post("/rental-payments/{id}/approve", [
        RentalPaymentController::class,
        "approve",
    ])->whereNumber("id");
    Route::post("/rental-payments/{id}/reject", [
        RentalPaymentController::class,
        "reject",
    ])->whereNumber("id");
    /*
    |--------------------------------------------------------------------------
    | FAMILY SYSTEM
    |--------------------------------------------------------------------------
    */
    Route::post("/family/join", [FamilyController::class, "join"]);

    /*
      |--------------------------------------------------------------------------
      | CONVERSATIONS
      |--------------------------------------------------------------------------
      */

    Route::get("/conversations", [ConversationController::class, "index"]);

    Route::post("/conversations", [ConversationController::class, "store"]);

    Route::get("/conversations/{id}", [
        ConversationController::class,
        "show",
    ])->whereNumber("id");

    Route::delete("/conversations/{id}", [
        ConversationController::class,
        "destroy",
    ])->whereNumber("id");

    /*
      |--------------------------------------------------------------------------
      | MESSAGES
      |--------------------------------------------------------------------------
      */

    Route::get("/conversations/{conversationId}/messages", [
        MessageController::class,
        "index",
    ])->whereNumber("conversationId");

    Route::post("/conversations/{conversationId}/messages", [
        MessageController::class,
        "store",
    ])->whereNumber("conversationId");

    Route::delete("/messages/{id}", [
        MessageController::class,
        "destroy",
    ])->whereNumber("id");
});
