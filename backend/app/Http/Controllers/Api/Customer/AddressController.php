<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;


class AddressController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | List Customer Addresses
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {

        $addresses =
            $request
                ->user()
                ->addresses()
                ->orderByDesc('is_default')
                ->orderByDesc('id')
                ->get();



        return response()->json([

            'success' => true,

            'data' => $addresses,

        ]);

    }





    /*
    |--------------------------------------------------------------------------
    | Create Address
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {

        $validated =
            $request->validate([


                'full_name' => [
                    'required',
                    'string',
                    'max:255',
                ],


                'phone' => [
                    'required',
                    'string',
                    'max:20',
                ],


                'address_line_1' => [
                    'required',
                    'string',
                    'max:255',
                ],


                'address_line_2' => [
                    'nullable',
                    'string',
                    'max:255',
                ],


                'landmark' => [
                    'nullable',
                    'string',
                    'max:255',
                ],


                'city' => [
                    'required',
                    'string',
                    'max:100',
                ],


                'state' => [
                    'required',
                    'string',
                    'max:100',
                ],


                'postal_code' => [
                    'required',
                    'string',
                    'max:20',
                ],


                'country' => [
                    'nullable',
                    'string',
                    'max:100',
                ],


                'type' => [
                    'required',
                    Rule::in([
                        'shipping',
                        'billing',
                        'both',
                    ]),
                ],


                'is_default' => [
                    'nullable',
                    'boolean',
                ],

            ]);



        $user =
            $request->user();




        $address =
            DB::transaction(

                function () use (
                    $validated,
                    $user
                ) {


                    $hasAddress =
                        $user
                            ->addresses()
                            ->exists();



                    $makeDefault =
                        !$hasAddress ||
                        ($validated['is_default'] ?? false);




                    if($makeDefault){

                        $user
                            ->addresses()
                            ->update([
                                'is_default'=>false
                            ]);

                    }




                    $address =
                        $user
                            ->addresses()
                            ->create([


                                'full_name' =>
                                    $validated['full_name'],


                                'phone' =>
                                    $validated['phone'],


                                'address_line_1' =>
                                    $validated['address_line_1'],


                                'address_line_2' =>
                                    $validated['address_line_2'] ?? null,


                                'landmark' =>
                                    $validated['landmark'] ?? null,


                                'city' =>
                                    $validated['city'],


                                'state' =>
                                    $validated['state'],


                                'postal_code' =>
                                    $validated['postal_code'],


                                'country' =>
                                    $validated['country'] ?? 'India',


                                'type' =>
                                    $validated['type'],


                                'is_default' =>
                                    $makeDefault,


                            ]);





                    if(
                        $makeDefault &&
                        in_array(
                            $validated['type'],
                            [
                                'shipping',
                                'both'
                            ]
                        )
                    ){

                        $user->update([

                            'phone' =>
                                $validated['phone']

                        ]);

                    }



                    return $address;


                }

            );




        return response()->json([

            'success'=>true,

            'message'=>
                'Address added successfully.',

            'data'=>$address,

        ],201);

    }







    /*
    |--------------------------------------------------------------------------
    | Show Address
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        Address $address
    ){

        $this->authorizeAddress(
            $request,
            $address
        );



        return response()->json([

            'success'=>true,

            'data'=>$address,

        ]);

    }







    /*
    |--------------------------------------------------------------------------
    | Update Address
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Address $address
    ){

        $this->authorizeAddress(
            $request,
            $address
        );



        $validated =
            $request->validate([


                'full_name'=>'required|string|max:255',

                'phone'=>'required|string|max:20',

                'address_line_1'=>'required|string|max:255',

                'address_line_2'=>'nullable|string|max:255',

                'landmark'=>'nullable|string|max:255',

                'city'=>'required|string|max:100',

                'state'=>'required|string|max:100',

                'postal_code'=>'required|string|max:20',

                'country'=>'nullable|string|max:100',


                'type'=>[
                    'required',
                    Rule::in([
                        'shipping',
                        'billing',
                        'both'
                    ])
                ],


                'is_default'=>'nullable|boolean',


            ]);





        DB::transaction(

            function () use (
                $request,
                $address,
                $validated
            ){



                $makeDefault =
                    $validated['is_default']
                    ??
                    $address->is_default;





                if($makeDefault){

                    $request
                        ->user()
                        ->addresses()
                        ->where(
                            'id',
                            '!=',
                            $address->id
                        )
                        ->update([

                            'is_default'=>false

                        ]);

                }







                $address->update([


                    'full_name'=>
                        $validated['full_name'],


                    'phone'=>
                        $validated['phone'],


                    'address_line_1'=>
                        $validated['address_line_1'],


                    'address_line_2'=>
                        $validated['address_line_2'] ?? null,


                    'landmark'=>
                        $validated['landmark'] ?? null,


                    'city'=>
                        $validated['city'],


                    'state'=>
                        $validated['state'],


                    'postal_code'=>
                        $validated['postal_code'],


                    'country'=>
                        $validated['country'] ?? 'India',


                    'type'=>
                        $validated['type'],


                    'is_default'=>
                        $makeDefault,


                ]);







                /*
                |--------------------------------------------------------------------------
                | Sync Address Phone To Profile
                |--------------------------------------------------------------------------
                */


                if(

                    $makeDefault &&

                    in_array(
                        $validated['type'],
                        [
                            'shipping',
                            'both'
                        ]
                    )

                ){

                    $request
                        ->user()
                        ->update([

                            'phone'=>
                                $validated['phone']

                        ]);

                }



            }

        );





        $address->refresh();




        return response()->json([

            'success'=>true,

            'message'=>
                'Address updated successfully.',

            'data'=>$address,

        ]);

    }







    /*
    |--------------------------------------------------------------------------
    | Delete Address
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        Address $address
    ){

        $this->authorizeAddress(
            $request,
            $address
        );



        $wasDefault =
            $address->is_default;



        $user =
            $request->user();




        DB::transaction(

            function() use(
                $address,
                $wasDefault,
                $user
            ){

                $address->delete();



                if($wasDefault){


                    $nextAddress =
                        $user
                            ->addresses()
                            ->latest('id')
                            ->first();



                    if($nextAddress){

                        $nextAddress->update([

                            'is_default'=>true

                        ]);

                    }


                }


            }

        );




        return response()->json([

            'success'=>true,

            'message'=>
                'Address deleted successfully.'

        ]);

    }







    /*
    |--------------------------------------------------------------------------
    | Set Default
    |--------------------------------------------------------------------------
    */

    public function setDefault(
        Request $request,
        Address $address
    ){

        $this->authorizeAddress(
            $request,
            $address
        );




        DB::transaction(

            function() use(
                $request,
                $address
            ){


                $request
                    ->user()
                    ->addresses()
                    ->update([

                        'is_default'=>false

                    ]);




                $address->update([

                    'is_default'=>true

                ]);





                if(
                    in_array(
                        $address->type,
                        [
                            'shipping',
                            'both'
                        ]
                    )
                ){

                    $request
                        ->user()
                        ->update([

                            'phone'=>
                                $address->phone

                        ]);

                }



            }

        );





        return response()->json([

            'success'=>true,

            'message'=>
                'Default address updated successfully.',


            'data'=>
                $address->fresh(),

        ]);

    }







    /*
    |--------------------------------------------------------------------------
    | Ownership Protection
    |--------------------------------------------------------------------------
    */

    private function authorizeAddress(
        Request $request,
        Address $address
    ): void {


        if(

            (int)$address->user_id !==
            (int)$request->user()->id

        ){

            abort(
                404,
                'Address not found.'
            );

        }

    }


}