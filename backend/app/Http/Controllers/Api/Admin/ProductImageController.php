<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Upload Multiple Images
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        Product $product
    ) {
        $request->validate([
            'images' => [
                'required',
                'array',
                'min:1',
            ],

            'images.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $existingImagesCount =
            $product->images()->count();

        $lastSortOrder =
            $product->images()
                ->max('sort_order');

        $nextSortOrder =
            $lastSortOrder !== null
                ? $lastSortOrder + 1
                : 0;

        $uploadedImages = [];

        foreach (
            $request->file('images')
            as $index => $file
        ) {
            $path = $file->store(
                'products',
                'public'
            );

            $image =
                ProductImage::create([
                    'product_id' =>
                        $product->id,

                    'image' => $path,

                    'alt_text' =>
                        $product->name,

                    'sort_order' =>
                        $nextSortOrder +
                        $index,

                    'is_primary' =>
                        $existingImagesCount === 0
                        && $index === 0,
                ]);

            $uploadedImages[] =
                $image;
        }

        return response()->json([
            'success' => true,

            'message' =>
                'Product images uploaded successfully.',

            'data' =>
                $uploadedImages,
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Image
    |--------------------------------------------------------------------------
    */

    public function destroy(
        ProductImage $image
    ) {
        $productId =
            $image->product_id;

        $wasPrimary =
            $image->is_primary;

        if (
            $image->image &&
            Storage::disk('public')
                ->exists($image->image)
        ) {
            Storage::disk('public')
                ->delete($image->image);
        }

        $image->delete();

        /*
        |--------------------------------------------------------------------------
        | If primary image was deleted,
        | make the next available image primary
        |--------------------------------------------------------------------------
        */

        if ($wasPrimary) {
            $nextImage =
                ProductImage::where(
                    'product_id',
                    $productId
                )
                    ->orderBy(
                        'sort_order'
                    )
                    ->orderBy('id')
                    ->first();

            if ($nextImage) {
                $nextImage->update([
                    'is_primary' => true,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' =>
                'Product image deleted successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Set Primary Image
    |--------------------------------------------------------------------------
    */

    public function primary(
        ProductImage $image
    ) {
        ProductImage::where(
            'product_id',
            $image->product_id
        )->update([
            'is_primary' => false,
        ]);

        $image->update([
            'is_primary' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Primary image updated successfully.',
            'data' => $image,
        ]);
    }
}