<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Customer List
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $customers = User::query()
            ->where('role', 'customer')

            ->when(
                $request->search,
                function ($query) use ($request) {

                    $search =
                        $request->search;

                    $query->where(function ($q) use ($search) {

                        $q->where(
                            'name',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'email',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhere(
                            'phone',
                            'like',
                            "%{$search}%"
                        );

                    });

                }
            )

            ->latest()

            ->paginate(20);


        return response()->json([

            'success'=>true,

            'data'=>$customers

        ]);
    }



    /*
    |--------------------------------------------------------------------------
    | Customer Details
    |--------------------------------------------------------------------------
    */

    public function show($id)
    {

        $customer =
            User::where(
                'role',
                'customer'
            )
            ->findOrFail($id);



        return response()->json([

            'success'=>true,


            'data'=>[

                'id'=>
                    $customer->id,


                'name'=>
                    $customer->name,


                'email'=>
                    $customer->email,


                'phone'=>
                    $customer->phone,


                'status'=>
                    $customer->status,


                'created_at'=>
                    $customer->created_at,


            ]

        ]);

    }



    /*
    |--------------------------------------------------------------------------
    | Update Customer
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        $id
    )
    {

        $customer =
            User::where(
                'role',
                'customer'
            )
            ->findOrFail($id);



        $validated =
            $request->validate([

                'name'=>
                [
                    'required',
                    'string',
                    'max:100'
                ],


                'email'=>
                [
                    'required',
                    'email'
                ],


                'phone'=>
                [
                    'nullable',
                    'string',
                    'max:20'
                ],


                'status'=>
                [
                    'required',
                    'in:active,inactive'
                ]

            ]);



        $customer->update(
            $validated
        );



        return response()->json([

            'success'=>true,

            'message'=>
                'Customer updated successfully.',

            'data'=>$customer

        ]);

    }


}