"use client";

import {
  useEffect,
  useState,
} from "react";

import Link from "next/link";

import {
  ArrowLeft,
  Save,
  UserRound,
  Mail,
  Phone,
  CheckCircle2,
} from "lucide-react";

import {
  useParams,
  useRouter,
} from "next/navigation";

import {
  apiFetch,
} from "@/lib/api";


type Customer = {

  id:number;

  name:string;

  email:string;

  phone:string | null;

  status:string;

  created_at:string;

};



export default function EditCustomerPage(){

  const params =
    useParams();


  const router =
    useRouter();


  const customerId =
    String(params.id);



  const [
    loading,
    setLoading
  ] =
  useState(true);



  const [
    saving,
    setSaving
  ] =
  useState(false);



  const [
    error,
    setError
  ] =
  useState("");



  const [
    name,
    setName
  ] =
  useState("");



  const [
    email,
    setEmail
  ] =
  useState("");



  const [
    phone,
    setPhone
  ] =
  useState("");



  const [
    status,
    setStatus
  ] =
  useState("active");





  useEffect(()=>{

    loadCustomer();

  },[]);






  async function loadCustomer(){

    try{

      setLoading(true);


      const response =
        await apiFetch(
          `/admin/customers/${customerId}`
        );



      const data =
        await response.json();



      if(!response.ok){

        throw new Error(
          data.message ||
          "Customer not found"
        );

      }



      const customer:
        Customer =
        data.data;



      setName(
        customer.name
      );


      setEmail(
        customer.email
      );


      setPhone(
        customer.phone || ""
      );


      setStatus(
        customer.status
      );


    }
    catch(error:any){

      setError(
        error.message
      );

    }
    finally{

      setLoading(false);

    }

  }







  async function saveCustomer(){

    try{

      setSaving(true);

      setError("");



      const response =
        await apiFetch(
          `/admin/customers/${customerId}`,
          {

            method:"PUT",

            body:JSON.stringify({

              name,

              email,

              phone,

              status,

            }),

          }
        );



      const data =
        await response.json();



      if(!response.ok){

        throw new Error(
          data.message ||
          "Unable to update customer"
        );

      }



      router.push(
        `/admin/customers/${customerId}`
      );


    }
    catch(error:any){

      setError(
        error.message
      );

    }
    finally{

      setSaving(false);

    }

  }






  if(loading){

    return (

      <div className="flex min-h-[400px] items-center justify-center">

        Loading customer...

      </div>

    );

  }





  return (

    <main className="min-h-screen bg-gray-50 p-6">


      <div className="mx-auto max-w-3xl">



        <Link

          href={`/admin/customers/${customerId}`}

          className="mb-6 inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900"

        >

          <ArrowLeft size={17}/>

          Back to Customer

        </Link>





        <section className="rounded-xl border border-gray-200 bg-white">



          <div className="border-b border-gray-200 px-6 py-5">


            <h1 className="text-xl font-semibold text-gray-900">

              Edit Customer

            </h1>


            <p className="mt-1 text-sm text-gray-500">

              Update customer account information.

            </p>


          </div>







          <div className="p-6">


            {
              error && (

                <div className="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">

                  {error}

                </div>

              )
            }







            <div className="space-y-5">





              {/* Name */}

              <div>

                <label className="mb-2 block text-sm font-medium text-gray-700">

                  Full Name

                </label>


                <div className="relative">

                  <UserRound
                    size={18}
                    className="absolute left-3 top-3.5 text-gray-400"
                  />


                  <input

                    value={name}

                    onChange={
                      e=>setName(
                        e.target.value
                      )
                    }

                    className="w-full rounded-lg border border-gray-300 py-3 pl-10 pr-4 text-sm outline-none focus:border-gray-700"

                  />

                </div>


              </div>







              {/* Email */}

              <div>

                <label className="mb-2 block text-sm font-medium text-gray-700">

                  Email Address

                </label>


                <div className="relative">

                  <Mail
                    size={18}
                    className="absolute left-3 top-3.5 text-gray-400"
                  />


                  <input

                    type="email"

                    value={email}

                    onChange={
                      e=>setEmail(
                        e.target.value
                      )
                    }

                    className="w-full rounded-lg border border-gray-300 py-3 pl-10 pr-4 text-sm outline-none focus:border-gray-700"

                  />


                </div>


              </div>







              {/* Phone */}

              <div>

                <label className="mb-2 block text-sm font-medium text-gray-700">

                  Phone Number

                </label>



                <div className="relative">


                  <Phone
                    size={18}
                    className="absolute left-3 top-3.5 text-gray-400"
                  />



                  <input

                    value={phone}

                    onChange={
                      e=>setPhone(
                        e.target.value
                      )
                    }


                    placeholder="Enter phone number"


                    className="w-full rounded-lg border border-gray-300 py-3 pl-10 pr-4 text-sm outline-none focus:border-gray-700"

                  />


                </div>


              </div>







              {/* Status */}

              <div>


                <label className="mb-2 block text-sm font-medium text-gray-700">

                  Account Status

                </label>



                <select

                  value={status}

                  onChange={
                    e=>setStatus(
                      e.target.value
                    )
                  }


                  className="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm outline-none focus:border-gray-700"

                >

                  <option value="active">

                    Active

                  </option>



                  <option value="inactive">

                    Inactive

                  </option>


                </select>



              </div>







              {/* Buttons */}


              <div className="flex justify-end gap-3 border-t pt-5">


                <Link

                  href={`/admin/customers/${customerId}`}

                  className="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50"

                >

                  Cancel

                </Link>





                <button

                  onClick={saveCustomer}

                  disabled={saving}

                  className="flex items-center gap-2 rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-black disabled:opacity-50"

                >

                  {
                    saving
                    ?
                    "Saving..."
                    :
                    <>
                      <Save size={17}/>
                      Save Changes
                    </>
                  }


                </button>


              </div>



            </div>


          </div>




        </section>


      </div>


    </main>

  );

}