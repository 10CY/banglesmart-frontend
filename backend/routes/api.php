<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\SizeController;
use App\Http\Controllers\Api\Admin\ColorController;
use App\Http\Controllers\Api\Admin\ProductController;
use App\Http\Controllers\Api\Admin\ProductImageController;
use App\Http\Controllers\Api\Admin\ProductVariantController;
use App\Http\Controllers\Api\Admin\InventoryController;
use App\Http\Controllers\Api\Admin\InventoryMovementController;
use App\Http\Controllers\Api\Admin\CustomerController;

use App\Http\Controllers\Api\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Api\Customer\AddressController;
use App\Http\Controllers\Api\Customer\CartController;

use App\Http\Controllers\Api\Store\ProductController as StoreProductController;
use App\Http\Controllers\Api\Customer\OrderController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Customer\WishlistController;

use App\Http\Controllers\Api\Admin\CouponController as AdminCouponController;

use App\Http\Controllers\Api\Customer\CouponController as CustomerCouponController;
use App\Http\Controllers\Api\Admin\ShippingSettingController as AdminShippingSettingController;

use App\Http\Controllers\Api\Customer\ShippingController as CustomerShippingController;

use App\Http\Controllers\Api\Admin\DashboardController;

use App\Http\Controllers\Api\Customer\ProfileController;
use App\Http\Controllers\Api\Customer\InvoiceController as CustomerInvoiceController;
use App\Http\Controllers\Api\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Api\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Api\Customer\ReviewController as CustomerReviewController;

/*
|--------------------------------------------------------------------------
| Public API Test
|--------------------------------------------------------------------------
*/

Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'BanglesMart API working',
    ]);
});


/*
|--------------------------------------------------------------------------
| CUSTOMER
|--------------------------------------------------------------------------
*/

Route::prefix('customer')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/register',
        [
            CustomerAuthController::class,
            'register',
        ]
    );

    Route::post(
        '/login',
        [
            CustomerAuthController::class,
            'login',
        ]
    );
    

    /*
    |--------------------------------------------------------------------------
    | Protected Customer Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        Route::get(
            '/me',
            [
                CustomerAuthController::class,
                'me',
            ]
        );

        Route::post(
            '/logout',
            [
                CustomerAuthController::class,
                'logout',
            ]
        );
        /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/profile',
        [
            ProfileController::class,
            'show',
        ]
    );

    Route::put(
        '/profile',
        [
            ProfileController::class,
            'update',
        ]
    );

    Route::put(
        '/profile/password',
        [
            ProfileController::class,
            'changePassword',
        ]
    );


        /*
        |--------------------------------------------------------------------------
        | Addresses
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/addresses',
            [
                AddressController::class,
                'index',
            ]
        );

        Route::post(
            '/addresses',
            [
                AddressController::class,
                'store',
            ]
        );

        Route::get(
            '/addresses/{address}',
            [
                AddressController::class,
                'show',
            ]
        );

        Route::put(
            '/addresses/{address}',
            [
                AddressController::class,
                'update',
            ]
        );

        Route::delete(
            '/addresses/{address}',
            [
                AddressController::class,
                'destroy',
            ]
        );

        Route::put(
            '/addresses/{address}/default',
            [
                AddressController::class,
                'setDefault',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Cart
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/cart',
            [
                CartController::class,
                'index',
            ]
        );

        Route::post(
            '/cart/items',
            [
                CartController::class,
                'store',
            ]
        );

        Route::put(
            '/cart/items/{cartItem}',
            [
                CartController::class,
                'update',
            ]
        );

        Route::delete(
            '/cart/items/{cartItem}',
            [
                CartController::class,
                'destroy',
            ]
        );

        Route::delete(
            '/cart',
            [
                CartController::class,
                'clear',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Orders / Checkout
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/orders',
            [
                OrderController::class,
                'index',
            ]
        );

        Route::post(
            '/orders',
            [
                OrderController::class,
                'store',
            ]
        );

        Route::get(
            '/orders/{order}',
            [
                OrderController::class,
                'show',
            ]
        );

        Route::get(
            '/orders/{order}/invoice',
            [
                CustomerInvoiceController::class,
                'download',
            ]
        );
        Route::post(
            '/orders/{order}/cancel',
            [
                OrderController::class,
                'cancel',
            ]
        );

        Route::post('/products/{product}/reviews', [CustomerReviewController::class, 'store']);
        Route::get('/products/{product}/reviews/mine', [CustomerReviewController::class, 'mine']);

        /*
        |--------------------------------------------------------------------------
        | Wishlist
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/wishlist',
            [
                WishlistController::class,
                'index',
            ]
        );

        Route::post(
            '/wishlist',
            [
                WishlistController::class,
                'store',
            ]
        );

        Route::get(
            '/wishlist/check/{product}',
            [
                WishlistController::class,
                'check',
            ]
        );

        Route::delete(
            '/wishlist/{wishlistItem}',
            [
                WishlistController::class,
                'destroy',
            ]
        );
        /*
        |--------------------------------------------------------------------------
        | Coupon Validation
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/coupons/validate',
            [
                CustomerCouponController::class,
                'validateCoupon',
            ]
        );
        
        /*
        |--------------------------------------------------------------------------
        | Shipping Quote
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/shipping/quote',
            [
                CustomerShippingController::class,
                'quote',
            ]
        );

    });

});


/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Admin Login
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/login',
        [
            AdminAuthController::class,
            'login',
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Protected Admin Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        Route::get(
            '/me',
            [
                AdminAuthController::class,
                'me',
            ]
        );
        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            [
                DashboardController::class,
                'index',
            ]
        );

        Route::post(
            '/logout',
            [
                AdminAuthController::class,
                'logout',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/categories',
            [
                CategoryController::class,
                'index',
            ]
        );

        Route::post(
            '/categories',
            [
                CategoryController::class,
                'store',
            ]
        );

        Route::put(
            '/categories/{category}',
            [
                CategoryController::class,
                'update',
            ]
        );

        Route::delete(
            '/categories/{category}',
            [
                CategoryController::class,
                'destroy',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Sizes
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/sizes',
            [
                SizeController::class,
                'index',
            ]
        );

        Route::post(
            '/sizes',
            [
                SizeController::class,
                'store',
            ]
        );

        Route::put(
            '/sizes/{size}',
            [
                SizeController::class,
                'update',
            ]
        );

        Route::delete(
            '/sizes/{size}',
            [
                SizeController::class,
                'destroy',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Colors
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/colors',
            [
                ColorController::class,
                'index',
            ]
        );

        Route::post(
            '/colors',
            [
                ColorController::class,
                'store',
            ]
        );

        Route::put(
            '/colors/{color}',
            [
                ColorController::class,
                'update',
            ]
        );

        Route::delete(
            '/colors/{color}',
            [
                ColorController::class,
                'destroy',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/products',
            [
                ProductController::class,
                'index',
            ]
        );

        Route::post(
            '/products',
            [
                ProductController::class,
                'store',
            ]
        );

        Route::get(
            '/products/{product}',
            [
                ProductController::class,
                'show',
            ]
        );

        Route::put(
            '/products/{product}',
            [
                ProductController::class,
                'update',
            ]
        );

        Route::delete(
            '/products/{product}',
            [
                ProductController::class,
                'destroy',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Product Images
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/products/{product}/images',
            [
                ProductImageController::class,
                'store',
            ]
        );

        Route::delete(
            '/product-images/{image}',
            [
                ProductImageController::class,
                'destroy',
            ]
        );

        Route::put(
            '/product-images/{image}/primary',
            [
                ProductImageController::class,
                'primary',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Product Variants
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/products/{product}/variants',
            [
                ProductVariantController::class,
                'index',
            ]
        );

        Route::post(
            '/products/{product}/variants',
            [
                ProductVariantController::class,
                'store',
            ]
        );

        Route::put(
            '/product-variants/{variant}',
            [
                ProductVariantController::class,
                'update',
            ]
        );

        Route::delete(
            '/product-variants/{variant}',
            [
                ProductVariantController::class,
                'destroy',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Inventory
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/inventory',
            [
                InventoryController::class,
                'index',
            ]
        );

        Route::put(
            '/inventory/{inventory}',
            [
                InventoryController::class,
                'update',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Inventory History
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/inventory-movements',
            [
                InventoryMovementController::class,
                'index',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/customers',
            [
                CustomerController::class,
                'index',
            ]
        );

        Route::get(
            '/customers/{customer}',
            [
                CustomerController::class,
                'show',
            ]
        );

        Route::put(
            '/customers/{customer}',
            [
                CustomerController::class,
                'update',
            ]
        );

        Route::put(
            '/customers/{customer}/status',
            [
                CustomerController::class,
                'updateStatus',
            ]
        );
        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/orders',
            [
                AdminOrderController::class,
                'index',
            ]
        );

        Route::get(
            '/orders/{order}',
            [
                AdminOrderController::class,
                'show',
            ]
        );

        Route::get(
            '/orders/{order}/invoice',
            [
                AdminInvoiceController::class,
                'download',
            ]
        );

        Route::put(
            '/orders/{order}/status',
            [
                AdminOrderController::class,
                'updateStatus',
            ]
        );

        Route::put(
            '/orders/{order}/shipping',
            [
                AdminOrderController::class,
                'updateShipping',
            ]
        );

        Route::get('/reviews', [AdminReviewController::class, 'index']);
        Route::put('/reviews/{review}', [AdminReviewController::class, 'update']);
        Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | Coupons / Discounts
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/coupons',
            [
                AdminCouponController::class,
                'index',
            ]
        );

        Route::post(
            '/coupons',
            [
                AdminCouponController::class,
                'store',
            ]
        );

        Route::get(
            '/coupons/{coupon}',
            [
                AdminCouponController::class,
                'show',
            ]
        );

        Route::put(
            '/coupons/{coupon}',
            [
                AdminCouponController::class,
                'update',
            ]
        );

        Route::delete(
            '/coupons/{coupon}',
            [
                AdminCouponController::class,
                'destroy',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Shipping Settings
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/shipping-settings',
            [
                AdminShippingSettingController::class,
                'show',
            ]
        );

        Route::put(
            '/shipping-settings',
            [
                AdminShippingSettingController::class,
                'update',
            ]
        );

    });

}); // IMPORTANT: ADMIN GROUP ENDS HERE


/*
|--------------------------------------------------------------------------
| PUBLIC STORE
|--------------------------------------------------------------------------
|
| IMPORTANT:
| This must remain OUTSIDE the admin and customer groups.
|
*/

Route::prefix('store')->group(function () {

    Route::get(
        '/products',
        [
            StoreProductController::class,
            'index',
        ]
    );

    Route::get(
        '/products/{slug}',
        [
            StoreProductController::class,
            'show',
        ]
    );

    Route::get(
        '/categories',
        [
            StoreProductController::class,
            'categories',
        ]
    );

});