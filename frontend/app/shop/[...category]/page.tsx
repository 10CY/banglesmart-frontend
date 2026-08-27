import { notFound } from "next/navigation";

import { API_URL } from "@/lib/api";

import CategoryFilters, {
  FilterCategory,
} from "@/components/store/CategoryFilters";

import ProductCard, {
  StoreProductCardData,
} from "@/components/store/ProductCard";

/* =========================================================
   TYPES
========================================================= */

type Category = {
  id: number;
  name: string;
  slug: string;
  parent_id: number | null;
  description?: string | null;
  image?: string | null;
  status?: string;
  sort_order?: number;
  children?: Category[];
};

type Product = StoreProductCardData & {
  category_id?: number;
  short_description?: string | null;
  description?: string | null;
};

type PageProps = {
  params: Promise<{
    category: string[];
  }>;

  searchParams: Promise<{
    search?: string;
    min_price?: string;
    max_price?: string;
    sort?: string;
    featured?: string;
    best_seller?: string;
    new_arrival?: string;
  }>;
};

/* =========================================================
   GET CATEGORIES
========================================================= */

async function getCategories(): Promise<Category[]> {
  try {
    const response = await fetch(
      `${API_URL}/store/categories`,
      {
        headers: {
          Accept: "application/json",
        },
        cache: "no-store",
      }
    );

    if (!response.ok) {
      console.error(
        "Categories API failed:",
        response.status
      );

      return [];
    }

    const json = await response.json();

    if (!Array.isArray(json?.data)) {
      return [];
    }

    /*
     * IMPORTANT:
     *
     * Keep the nested children structure.
     *
     * Parent
     *   children[]
     */

    return json.data.map((item: any) => ({
      id: Number(item.id),

      name: item.name,

      slug: item.slug,

      parent_id:
        item.parent_id === null ||
        item.parent_id === undefined
          ? null
          : Number(item.parent_id),

      description: item.description ?? null,

      image: item.image ?? null,

      status: item.status ?? "active",

      sort_order: Number(
        item.sort_order ?? 0
      ),

      children: Array.isArray(item.children)
        ? item.children.map((child: any) => ({
            id: Number(child.id),

            name: child.name,

            slug: child.slug,

            parent_id:
              child.parent_id === null ||
              child.parent_id === undefined
                ? null
                : Number(child.parent_id),

            description:
              child.description ?? null,

            image: child.image ?? null,

            status:
              child.status ?? "active",

            sort_order: Number(
              child.sort_order ?? 0
            ),

            children: [],
          }))
        : [],
    }));
  } catch (error) {
    console.error(
      "Categories request failed:",
      error
    );

    return [];
  }
}

/* =========================================================
   GET PRODUCTS
========================================================= */

async function getProducts(
  categorySlug: string,
  filters: {
    search?: string;
    min_price?: string;
    max_price?: string;
    sort?: string;
    featured?: string;
    best_seller?: string;
    new_arrival?: string;
  }
): Promise<Product[]> {
  try {
    const params = new URLSearchParams();

    /*
     * CATEGORY
     *
     * Parent:
     * /shop/glass-bangles
     *
     * Child:
     * /shop/glass-bangles/bridal-glass-bangles
     */

    params.set(
      "category",
      categorySlug
    );

    params.set(
      "per_page",
      "24"
    );

    /* SEARCH */

    if (filters.search) {
      params.set(
        "search",
        filters.search
      );
    }

    /* PRICE */

    if (filters.min_price) {
      params.set(
        "min_price",
        filters.min_price
      );
    }

    if (filters.max_price) {
      params.set(
        "max_price",
        filters.max_price
      );
    }

    /* SORT */

    if (filters.sort) {
      params.set(
        "sort",
        filters.sort
      );
    }

    /* COLLECTION */

    if (filters.featured === "1") {
      params.set(
        "featured",
        "1"
      );
    }

    if (filters.best_seller === "1") {
      params.set(
        "best_seller",
        "1"
      );
    }

    if (filters.new_arrival === "1") {
      params.set(
        "new_arrival",
        "1"
      );
    }

    const url =
      `${API_URL}/store/products?${params.toString()}`;

    console.log(
      "CATEGORY PRODUCTS API:",
      url
    );

    const response = await fetch(
      url,
      {
        headers: {
          Accept:
            "application/json",
        },
        cache: "no-store",
      }
    );

    if (!response.ok) {
      console.error(
        "Products API failed:",
        response.status,
        url
      );

      return [];
    }

    const json =
      await response.json();

    /*
     * Laravel pagination:
     *
     * data.data = products
     */

    if (
      Array.isArray(
        json?.data?.data
      )
    ) {
      return json.data.data;
    }

    /*
     * Non-paginated response
     */

    if (
      Array.isArray(
        json?.data
      )
    ) {
      return json.data;
    }

    return [];
  } catch (error) {
    console.error(
      "Products request failed:",
      error
    );

    return [];
  }
}

/* =========================================================
   PAGE
========================================================= */

export default async function CategoryPage({
  params,
  searchParams,
}: PageProps) {
  const {
    category: segments,
  } = await params;

  const filters =
    await searchParams;

  /* =======================================================
     VALIDATE URL
  ======================================================= */

  if (
    !segments ||
    segments.length === 0 ||
    segments.length > 2
  ) {
    notFound();
  }

  /* =======================================================
     GET CATEGORIES
  ======================================================= */

  const categories =
    await getCategories();

  if (!categories.length) {
    notFound();
  }

  /* =======================================================
     FIND PARENT
  ======================================================= */

  const parent =
    categories.find(
      (item) =>
        item.slug === segments[0] &&
        item.parent_id === null
    );

  if (!parent) {
    notFound();
  }

  /* =======================================================
     SELECTED CATEGORY
  ======================================================= */

  let selectedCategory: Category =
    parent;

  let child: Category | null = null;

  /* =======================================================
     SUBCATEGORY
  ======================================================= */

  if (segments.length === 2) {
    child =
      parent.children?.find(
        (item) =>
          item.slug === segments[1]
      ) || null;

    if (!child) {
      notFound();
    }

    selectedCategory = child;
  }

  /* =======================================================
     GET PRODUCTS
  ======================================================= */

  const products =
    await getProducts(
      selectedCategory.slug,
      filters
    );

  /* =======================================================
     FILTER CATEGORY TREE
  ======================================================= */

  const filterCategories: FilterCategory[] =
    categories.map((item) => ({
      id: item.id,

      name: item.name,

      slug: item.slug,

      parent_id: item.parent_id,

      children:
        item.children?.map(
          (childItem) => ({
            id: childItem.id,

            name: childItem.name,

            slug: childItem.slug,

            parent_id:
              childItem.parent_id,

            children: [],
          })
        ) || [],
    }));

  /* =======================================================
     CURRENT CATEGORY
  ======================================================= */

  const currentFilterCategory =
    selectedCategory.slug;

  /* =======================================================
     RENDER
  ======================================================= */

  return (
    <main className="min-h-screen bg-white">

      {/* ===================================================
          HERO
      =================================================== */}

      <section className="border-b border-[#e9e1d7] bg-[#f7f1e8]">

        <div className="mx-auto max-w-7xl px-6 py-14 sm:py-16">

          <p className="text-[10px] font-semibold uppercase tracking-[0.25em] text-[#c9a227]">
            BanglesMart / Collection
          </p>

          <h1 className="mt-4 font-[family-name:var(--font-playfair)] text-4xl text-[#111] sm:text-5xl">
            {selectedCategory.name}
          </h1>

          <p className="mt-4 max-w-2xl text-sm leading-7 text-[#666]">
            {selectedCategory.description ||
              `Explore our beautiful collection of ${selectedCategory.name.toLowerCase()}.`}
          </p>

        </div>

      </section>

      {/* ===================================================
          BREADCRUMB
      =================================================== */}

      <section className="mx-auto max-w-7xl px-6 pt-8">

        <div className="flex flex-wrap items-center gap-2 text-sm">

          <a
            href="/shop"
            className="text-[#777] transition hover:text-[#111]"
          >
            Shop
          </a>

          <span className="text-[#aaa]">
            ›
          </span>

          <a
            href={`/shop/${parent.slug}`}
            className={
              child
                ? "text-[#777] transition hover:text-[#111]"
                : "font-medium text-[#111]"
            }
          >
            {parent.name}
          </a>

          {child && (
            <>
              <span className="text-[#aaa]">
                ›
              </span>

              <span className="font-medium text-[#111]">
                {child.name}
              </span>
            </>
          )}

        </div>

      </section>

      {/* ===================================================
          PRODUCTS
      =================================================== */}

      <section className="mx-auto max-w-7xl px-6 py-10">

        <div className="grid gap-10 lg:grid-cols-[250px_minmax(0,1fr)] xl:grid-cols-[270px_minmax(0,1fr)]">

          {/* =================================================
              SIDEBAR FILTER
          ================================================= */}

          <aside className="hidden lg:block">

            <div className="sticky top-28 rounded-2xl border border-[#e7dfd4] bg-white p-5 shadow-[0_8px_30px_rgba(50,35,20,.035)]">

              <div className="mb-5">

                <h3 className="text-sm font-semibold text-[#191919]">
                  Refine
                </h3>

                <p className="mt-1 text-[10px] text-[#999]">
                  Find your perfect
                  bangles
                </p>

              </div>

              <CategoryFilters
                categories={ 
                  filterCategories
                }

                category={
                  currentFilterCategory
                }

                productCount={
                  products.length
                }

                search={
                  filters.search || ""
                }

                minPrice={
                  filters.min_price ||
                  ""
                }

                maxPrice={
                  filters.max_price ||
                  ""
                }

                sort={
                  filters.sort || ""
                }

                newArrival={
                  filters.new_arrival ===
                  "1"
                }

                bestSeller={
                  filters.best_seller ===
                  "1"
                }

                featured={
                  filters.featured ===
                  "1"
                }
              />

            </div>

          </aside>

          {/* =================================================
              PRODUCTS AREA
          ================================================= */}

          <div className="min-w-0">
 
            {/* HEADER */}

            <div className="mb-6 flex items-end justify-between border-b border-[#e9e1d7] pb-5">

              <div>

                <p className="text-[10px] uppercase tracking-[0.2em] text-[#999]">
                  Curated for you
                </p>

                <h2 className="mt-2 font-[family-name:var(--font-playfair)] text-3xl text-[#111]">
                  {selectedCategory.name}
                </h2>

                <p className="mt-1 text-sm text-[#777]">
                  {products.length}{" "}
                  {products.length ===
                  1
                    ? "product"
                    : "products"}
                </p>

              </div>

            </div>

            {/* =================================================
                PRODUCTS
            ================================================= */}

            {products.length === 0 ? (
              <div className="py-24 text-center">

                <div className="mx-auto max-w-md">

                  <p className="font-[family-name:var(--font-playfair)] text-2xl text-[#222]">
                    No products found
                  </p>

                  <p className="mt-3 text-sm leading-6 text-[#999]">
                    Try changing your
                    filters or browse
                    another collection.
                  </p>

                  <a
                    href={`/shop/${segments.join(
                      "/"
                    )}`}
                    className="mt-6 inline-flex rounded-full bg-[#8f0828] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#6f061f]"
                  >
                    Clear Filters
                  </a>

                </div>

              </div>
            ) : (
              <div className="grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-3 lg:grid-cols-3">

                {products.map(
                  (product) => (
                    <ProductCard
                      key={
                        product.id
                      }
                      product={
                        product
                      }
                    />
                  )
                )}

              </div>
            )}

          </div>

        </div>

      </section>

    </main>
  );
}