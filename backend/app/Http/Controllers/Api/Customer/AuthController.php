<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    */

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $customer = User::create([
            'name' => $validated['name'],

            'email' => strtolower(
                $validated['email']
            ),

            'password' => Hash::make(
                $validated['password']
            ),

            'role' => 'customer',

            'status' => 'active',
        ]);

        $token = $customer
            ->createToken('customer-token')
            ->plainTextToken;

        return response()->json([
            'success' => true,

            'message' =>
                'Account created successfully.',

            'token' => $token,

            'user' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'role' => $customer->role,
                'status' => $customer->status,
            ],
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);

        $customer = User::where(
            'email',
            strtolower($validated['email'])
        )->first();

        if (
            !$customer ||
            !Hash::check(
                $validated['password'],
                $customer->password
            )
        ) {
            throw ValidationException::withMessages([
                'email' => [
                    'Invalid email or password.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Only customers can use customer login
        |--------------------------------------------------------------------------
        */

        if ($customer->role !== 'customer') {
            return response()->json([
                'success' => false,

                'message' =>
                    'This account cannot use customer login.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Block inactive customers
        |--------------------------------------------------------------------------
        */

        if ($customer->status !== 'active') {
            return response()->json([
                'success' => false,

                'message' =>
                    'Your account is currently inactive.',
            ], 403);
        }

        $token = $customer
            ->createToken('customer-token')
            ->plainTextToken;

        return response()->json([
            'success' => true,

            'message' =>
                'Login successful.',

            'token' => $token,

            'user' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'role' => $customer->role,
                'status' => $customer->status,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Current Customer
    |--------------------------------------------------------------------------
    */

    public function me(Request $request)
    {
        $customer =
            $request->user();

        if (
            !$customer ||
            $customer->role !== 'customer'
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Customer not found.',
            ], 403);
        }

        if ($customer->status !== 'active') {
            $request
                ->user()
                ->currentAccessToken()
                ?->delete();

            return response()->json([
                'success' => false,

                'message' =>
                    'Your account is inactive.',
            ], 403);
        }

        return response()->json([
            'success' => true,

            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' =>
                $customer->phone,
                'role' => $customer->role,
                'status' => $customer->status,
                'created_at' =>
                    $customer->created_at,
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(Request $request)
    {
        $request
            ->user()
            ->currentAccessToken()
            ?->delete();

        return response()->json([
            'success' => true,

            'message' =>
                'Logged out successfully.',
        ]);
    }
}