<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingSetting;
use Illuminate\Http\Request;

class ShippingSettingController extends Controller
{
    public function show()
    {
        $setting =
            ShippingSetting::current();

        return response()->json([
            'success' =>
                true,

            'data' =>
                $setting,
        ]);
    }

    public function update(
        Request $request
    ) {
        $validated =
            $request->validate([
                'flat_shipping_amount' => [
                    'required',
                    'numeric',
                    'min:0',
                ],

                'free_shipping_minimum' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'shipping_enabled' => [
                    'required',
                    'boolean',
                ],
            ]);

        $setting =
            ShippingSetting::current();

        $setting->update(
            $validated
        );

        return response()->json([
            'success' =>
                true,

            'message' =>
                'Shipping settings updated successfully.',

            'data' =>
                $setting->fresh(),
        ]);
    }
}