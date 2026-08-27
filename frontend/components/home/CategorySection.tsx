"use client";

import { useEffect, useState } from "react";

import Link from "next/link";

import {
  ArrowRight,
} from "lucide-react";

import { storeApiFetch } from "@/lib/storeApi";


type Category = {

  id:number;

  name:string;

  slug:string;

  image?:string|null;

  children?:Category[];

};



export default function CategorySection(){


const [categories,setCategories] =
useState<Category[]>([]);


const [loading,setLoading] =
useState(true);



async function fetchCategories(){

try{

const response =
await storeApiFetch(
"/store/categories"
);


const json =
await response.json();



if(Array.isArray(json?.data)){


/*
Only parent categories
Remove subcategories
*/

const parents =
json.data.filter(
(cat:Category)=>
!cat.children ||
cat.children.length >= 0
);



setCategories(parents.slice(0,4));


}



}catch(error){

console.log(
"Category loading error",
error
);


}finally{

setLoading(false);

}


}



useEffect(()=>{

fetchCategories();

},[]);



if(loading){

return (

<section className="
mx-auto
max-w-7xl
px-6
py-16
">

<div className="
grid
grid-cols-2
gap-5
md:grid-cols-4
">

{
[1,2,3,4].map((i)=>(

<div
key={i}
className="
h-72
animate-pulse
rounded-2xl
bg-[#f5f0e8]
"
/>

))
}

</div>

</section>

);

}




return (

<section
className="
mx-auto
max-w-7xl
px-6
py-16
lg:px-10
"
>



{/* HEADER */}

<div
className="
flex
flex-col
items-center
justify-between
gap-4
text-center
md:flex-row
md:text-left
"
>


<div>


<p
className="
text-sm
uppercase
tracking-[0.25em]
text-[#C9A227]
"
>
Explore Collection
</p>


<h2
className="
mt-3
text-3xl
font-semibold
text-gray-900
md:text-4xl
"
>
Shop By Category
</h2>


<p
className="
mt-2
text-gray-500
"
>
Find jewellery designed for every beautiful moment.
</p>


</div>

<Link

href="/shop"

className="
flex
items-center
gap-2
text-sm
font-medium
text-[#C9A227]
"

>

View All

<ArrowRight size={16}/>

</Link>



</div>





{/* CATEGORY CARDS */}


<div
className="
mt-10
grid
grid-cols-2
gap-5
md:grid-cols-4
"
>


{
categories.map((category)=>(


<Link

key={category.id}

href={`/shop/${category.slug}`}

className="
group
overflow-hidden
rounded-2xl
bg-white
border
border-[#eee5d8]
transition
hover:-translate-y-1
hover:shadow-xl
"

>


<div
className="
relative
h-52
overflow-hidden
md:h-64
"
>


<img
src={`${process.env.NEXT_PUBLIC_BACKEND_URL}/storage/${category.image}`}
alt={category.name}
className="
h-full
w-full
object-cover
transition
duration-700
group-hover:scale-110
"
/>

<div
className="
absolute
inset-0
bg-gradient-to-t
from-black/40
to-transparent
"
/>

</div>

<div
className="
p-4
"
>

<h3
className="
text-base
font-semibold
text-gray-900
"
>

{category.name}

</h3>



<p
className="
mt-1
text-sm
text-gray-500
"
>

Explore Collection

</p>



</div>



</Link>


))
}



</div>



</section>

);

}