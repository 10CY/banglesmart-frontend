"use client";

import { useEffect, useState } from "react";

import CategoryFilters, {
  FilterCategory,
} from "@/components/store/CategoryFilters";

import ProductCard from "@/components/store/ProductCard";

import { storeApiFetch } from "@/lib/storeApi";


type Product = {
  id:number;
  name:string;
  slug:string;
  selling_price:number|string;
  mrp?:number|string;
  image?:string|null;
};



export default function CollectionPage({
  type,
  title,
}:{
  type:"new_arrival"|"best_seller";
  title:string;
}){


const [categories,setCategories] =
useState<FilterCategory[]>([]);


const [products,setProducts] =
useState<Product[]>([]);



const [category,setCategory] =
useState("");

const [search,setSearch] =
useState("");

const [minPrice,setMinPrice] =
useState("");

const [maxPrice,setMaxPrice] =
useState("");

const [sort,setSort] =
useState("");



const [newArrival,setNewArrival] =
useState(type==="new_arrival");


const [bestSeller,setBestSeller] =
useState(type==="best_seller");


const [featured,setFeatured] =
useState(false);




async function getCategories(){

const res =
await storeApiFetch(
"/store/categories"
);


const json =
await res.json();


if(Array.isArray(json?.data)){

setCategories(json.data);

}

}





async function getProducts(){


let url =
`/store/products?${type}=1`;



if(category)
url += `&category=${category}`;


if(search)
url += `&search=${search}`;


if(minPrice)
url += `&min_price=${minPrice}`;


if(maxPrice)
url += `&max_price=${maxPrice}`;


if(sort)
url += `&sort=${sort}`;



const res =
await storeApiFetch(url);


const json =
await res.json();



setProducts(
Array.isArray(json?.data?.data)
?
json.data.data
:
[]
);


}




useEffect(()=>{

getCategories();

},[]);



useEffect(()=>{

getProducts();

},[
category,
search,
minPrice,
maxPrice,
sort
]);




function clearFilters(){

setCategory("");
setSearch("");
setMinPrice("");
setMaxPrice("");
setSort("");

}



return (
  <main className="min-h-screen bg-[#fbfaf7]">

    {/* HERO */}
    <section className="border-b border-[#eee8de] bg-[#f5efe5]">
      <div className="mx-auto max-w-7xl px-5 py-12 sm:px-6 lg:px-8 lg:py-16">

        <p className="text-[10px] font-semibold uppercase tracking-[0.25em] text-[#8f0828]">
          BanglesMart / Collection
        </p>

        <h1 className="mt-3 font-[family-name:var(--font-playfair)] text-4xl leading-tight text-[#191919] sm:text-5xl">
          {title}
        </h1>

        <p className="mt-4 max-w-2xl text-sm leading-7 text-[#6d675e]">
          Explore our curated collection of premium bangles,
          traditional designs and latest trending jewellery
          crafted for every occasion.
        </p>

      </div>
    </section>


    {/* CONTENT */}

    <section className="mx-auto max-w-7xl px-5 py-10 sm:px-6 lg:px-8">

      <div className="mb-8 flex items-end justify-between border-b border-[#e9e2d8] pb-5">

        <div>

          <p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#a08f73]">
            Curated for you
          </p>


          <h2 className="mt-2 font-[family-name:var(--font-playfair)] text-3xl text-[#191919]">
            {title}
          </h2>


          <p className="mt-1 text-xs text-[#8b847a]">
            {products.length}{" "}
            {products.length === 1
              ? "product"
              : "products"}
          </p>

        </div>


      </div>



      <div className="grid gap-10 lg:grid-cols-[270px_minmax(0,1fr)]">


        {/* FILTER */}

        <aside className="hidden lg:block">

          <div className="sticky top-28 rounded-2xl border border-[#e7dfd4] bg-white p-5">

            <CategoryFilters

              categories={categories}

              category={category}
              setCategory={setCategory}

              search={search}
              setSearch={setSearch}

              minPrice={minPrice}
              maxPrice={maxPrice}

              setMinPrice={setMinPrice}
              setMaxPrice={setMaxPrice}

              sort={sort}
              setSort={setSort}


              newArrival={newArrival}
              bestSeller={bestSeller}
              featured={featured}


              setNewArrival={setNewArrival}
              setBestSeller={setBestSeller}
              setFeatured={setFeatured}


              clearFilters={clearFilters}

              productCount={products.length}

            />

          </div>

        </aside>



        {/* PRODUCTS */}

        <div>


          {
            products.length === 0 ? (

              <div className="py-20 text-center text-gray-400">
                No products found.
              </div>


            ) : (

              <div className="grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-3 xl:grid-cols-3">

                {
                  products.map((product)=>(

                    <ProductCard
                      key={product.id}
                      product={product}
                    />

                  ))
                }

              </div>

            )
          }


        </div>


      </div>


    </section>


  </main>
);

}