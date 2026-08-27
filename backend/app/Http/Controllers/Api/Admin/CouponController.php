<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | List
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query =
            Coupon::query()
                ->withCount('usages')
                ->latest();

        if (
            $request->filled(
                'search'
            )
        ) {
            $search =
                trim(
                    $request->search
                );

            $query->where(
                'code',
                'like',
                "%{$search}%"
            );
        }

        if (
            $request->filled(
                'status'
            )
        ) {
            $query->where(
                'status',
                $request->status
            );
        }

        if (
            $request->filled(
                'type'
            )
        ) {
            $query->where(
                'type',
                $request->type
            );
        }

        return response()->json([
            'success' => true,

            'data' =>
                $query->paginate(20),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated =
            $this->validateCoupon(
                $request
            );

        $validated['code'] =
            Str::upper(
                trim(
                    $validated['code']
                )
            );

        $coupon =
            Coupon::create(
                $validated
            );

        return response()->json([
            'success' => true,

            'message' =>
                'Coupon created successfully.',

            'data' =>
                $coupon,
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        Coupon $coupon
    ) {
        $coupon->loadCount(
            'usages'
        );

        return response()->json([
            'success' => true,

            'data' =>
                $coupon,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Coupon $coupon
    ) {
        $validated =
            $this->validateCoupon(
                $request,
                $coupon
            );

        $validated['code'] =
            Str::upper(
                trim(
                    $validated['code']
                )
            );

        $coupon->update(
            $validated
        );

        return response()->json([
            'success' => true,

            'message' =>
                'Coupon updated successfully.',

            'data' =>
                $coupon->fresh(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Coupon $coupon
    ) {
        if (
            $coupon
                ->usages()
                ->exists()
        ) {
            return response()->json([
                'success' => false,

                'message' =>
                    'This coupon has already been used. Disable it instead of deleting it.',
            ], 422);
        }

        $coupon->delete();

        return response()->json([
            'success' => true,

            'message' =>
                'Coupon deleted successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validateCoupon(
        Request $request,
        ?Coupon $coupon = null
    ): array {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',

                Rule::unique(
                    'coupons',
                    'code'
                )->ignore(
                    $coupon?->id
                ),
            ],

            'type' => [
                'required',

                Rule::in([
                    'fixed',
                    'percentage',
                ]),
            ],

            'value' => [
                'required',
                'numeric',
                'gt:0',

                function (
                    string $attribute,
                    mixed $value,
                    \Closure $fail
                ) use ($request) {

                    if (
                        $request->type ===
                            'percentage'
                        &&
                        (float) $value >
                            100
                    ) {
                        $fail(
                            'Percentage discount cannot exceed 100%.'
                        );
                    }
                },
            ],

            'minimum_order_amount' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'maximum_discount_amount' => [
                'nullable',
                'numeric',
                'gt:0',
            ],

            'usage_limit' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'per_user_limit' => [
                'required',
                'integer',
                'min:1',
            ],

            'starts_at' => [
                'nullable',
                'date',
            ],

            'expires_at' => [
                'nullable',
                'date',
                'after_or_equal:starts_at',
            ],

            'status' => [
                'required',

                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],
        ]);
    }
}