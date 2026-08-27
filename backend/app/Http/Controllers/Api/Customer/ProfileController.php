<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Models\Address;


class ProfileController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    public function show(Request $request)
    {
        $user = $request->user();


        return response()->json([

            'success' => true,


            'data' => [

                'id' =>
                    $user->id,


                'name' =>
                    $user->name,


                'email' =>
                    $user->email,


                'phone' =>
                    $user->phone,


                'status' =>
                    $user->status,


                'role' =>
                    $user->role,


                'created_at' =>
                    $user->created_at,

            ],

        ]);

    }



    /*
    |--------------------------------------------------------------------------
    | Update Profile
    |--------------------------------------------------------------------------
    */

    public function update(Request $request)
    {

        $user =
            $request->user();



        $validated =
            $request->validate([


                'name' => [

                    'required',

                    'string',

                    'max:100',

                ],



                'email' => [

                    'required',

                    'email',

                    'max:255',


                    Rule::unique(
                        'users',
                        'email'
                    )->ignore(
                        $user->id
                    ),

                ],



                'phone' => [

                    'nullable',

                    'string',

                    'max:20',

                    'regex:/^[0-9+\-\s()]+$/',

                ],


            ]);





        /*
        |--------------------------------------------------------------------------
        | Update User Profile
        |--------------------------------------------------------------------------
        */


        $user->update([


            'name' =>

                trim(
                    $validated['name']
                ),



            'email' =>

                strtolower(
                    trim(
                        $validated['email']
                    )
                ),



            'phone' =>

                !empty(
                    $validated['phone']
                )

                    ?

                    trim(
                        $validated['phone']
                    )

                    :

                    null,


        ]);






        /*
        |--------------------------------------------------------------------------
        | Sync Default Address Phone
        |--------------------------------------------------------------------------
        |
        | When customer changes profile phone,
        | update default shipping address phone also.
        |
        */


        if(
            !empty(
                $validated['phone']
            )
        ){


            Address::where(
    'user_id',
    $user->id
)
->where(
    'is_default',
    true
)
->where(
    'type',
    'shipping'
)
->update([

    'phone' =>
        trim(
            $validated['phone']
        ),

]);

        }







        $user->refresh();






        return response()->json([


            'success' => true,


            'message' =>

                'Profile updated successfully.',



            'data' => [


                'id' =>
                    $user->id,


                'name' =>
                    $user->name,


                'email' =>
                    $user->email,


                'phone' =>
                    $user->phone,


                'status' =>
                    $user->status,


                'role' =>
                    $user->role,


                'created_at' =>
                    $user->created_at,


            ],


        ]);

    }







    /*
    |--------------------------------------------------------------------------
    | Change Password
    |--------------------------------------------------------------------------
    */


    public function changePassword(
        Request $request
    )
    {

        $user =
            $request->user();




        $validated =
            $request->validate([


                'current_password' => [

                    'required',

                    'string',

                ],



                'password' => [

                    'required',

                    'confirmed',


                    Password::min(8)

                        ->letters()

                        ->numbers(),


                ],


            ]);







        if(
            !Hash::check(

                $validated['current_password'],

                $user->password

            )
        ){


            return response()->json([


                'success' => false,


                'message' =>

                    'Current password is incorrect.',



                'errors' => [


                    'current_password' => [


                        'Current password is incorrect.',


                    ],


                ],


            ],422);


        }







        if(

            Hash::check(

                $validated['password'],

                $user->password

            )

        ){


            return response()->json([


                'success' => false,


                'message' =>

                    'New password must be different from your current password.',



                'errors' => [


                    'password' => [


                        'New password must be different from your current password.',


                    ],


                ],


            ],422);


        }







        $user->update([


            'password' =>

                $validated['password'],


        ]);







        $currentToken =

            $user->currentAccessToken();




        if($currentToken){


            $user->tokens()

                ->where(

                    'id',

                    '!=',

                    $currentToken->id

                )

                ->delete();


        }







        return response()->json([


            'success' => true,


            'message' =>

                'Password changed successfully.',


        ]);

    }


}