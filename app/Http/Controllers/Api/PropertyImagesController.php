<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyImage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PropertyImagesController extends Controller
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    // helper format response
    private function formatImage($image)
    {
        return [
            "id" => $image->id,
            "url" => asset("storage/" . $image->url),
            "is_main" => $image->is_main,
            "properties_id" => $image->properties_id,
        ];
    }

    // =====================
    // GET ALL IMAGES
    // =====================
    public function index($propertyId)
    {
        $images = PropertyImage::where("properties_id", $propertyId)->get();

        return response()->json([
            "data" => $images->map(fn($img) => $this->formatImage($img)),
        ]);
    }

    // =====================
    // UPLOAD MULTIPLE IMAGES
    // =====================
    public function store(Request $request)
    {
        $request->validate([
            "images" => "required|array",
            "images.*" => "image|mimes:jpg,jpeg,png|max:4096",
        ]);

        $existingCount = PropertyImage::where(
            "properties_id",
            $request->properties_id,
        )->count();

        $createdImages = [];

        foreach ($request->file("images") as $index => $image) {
            $filename = Str::uuid() . ".jpg";
            $path = "properties/" . $filename;

            $img = $this->manager->read($image);

            $img->resize(1200, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            Storage::disk("public")->put($path, (string) $img->toJpeg(75));

            $isMain = $existingCount === 0 && $index === 0;

            $newImage = PropertyImage::create([
                "url" => $path,
                "is_main" => $isMain,
                "properties_id" => $propertyId, //dri url
            ]);

            $createdImages[] = $this->formatImage($newImage);
        }

        return response()->json(
            [
                "message" => "Images uploaded successfully",
                "data" => $createdImages,
            ],
            201,
        );
    }

    // =====================
    // DELETE IMAGE
    // =====================
    public function destroy($propertyId, $id)
    {
        $image = PropertyImage::where("properties_id", $propertyId)
            ->where("id", $id)
            ->firstOrFail();

        if (Storage::disk("public")->exists($image->url)) {
            Storage::disk("public")->delete($image->url);
        }

        $wasMain = $image->is_main;

        $image->delete();

        if ($wasMain) {
            $newMain = PropertyImage::where(
                "properties_id",
                $propertyId,
            )->first();
            if ($newMain) {
                $newMain->update(["is_main" => true]);
            }
        }

        return response()->json([
            "message" => "Image deleted successfully",
        ]);
    }

    // =====================
    // SET MAIN IMAGE
    // =====================
    public function setMain($propertyId, $id)
    {
        $image = PropertyImage::where("properties_id", $propertyId)
            ->where("id", $id)
            ->firstOrFail();

        PropertyImage::where("properties_id", $propertyId)->update([
            "is_main" => false,
        ]);

        $image->update(["is_main" => true]);

        return response()->json([
            "message" => "Main image updated",
            "data" => $this->formatImage($image),
        ]);
    }

    // =====================
    // UPDATE (REPLACE IMAGE)
    // =====================
    public function update(Request $request, $propertyId, $id)
    {
        $request->validate([
            "image" => "required|image|mimes:jpg,jpeg,png|max:4096",
        ]);

        $image = PropertyImage::where("properties_id", $propertyId)
            ->where("id", $id)
            ->firstOrFail();

        if (Storage::disk("public")->exists($image->url)) {
            Storage::disk("public")->delete($image->url);
        }

        $filename = Str::uuid() . ".jpg";
        $path = "properties/" . $filename;

        $img = $this->manager->read($request->file("image"));

        $img->resize(1200, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        Storage::disk("public")->put($path, (string) $img->toJpeg(75));

        $image->update([
            "url" => $path,
        ]);

        return response()->json([
            "message" => "Image updated successfully",
            "data" => $this->formatImage($image),
        ]);
    }
}
