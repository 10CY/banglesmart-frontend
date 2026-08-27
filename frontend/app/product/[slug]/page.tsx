"use client";

import {
  useCallback,
  useEffect,
  useMemo,
  useState,
} from "react";

import Link from "next/link";

import {
  ArrowLeft,
  Check,
  Heart,
  Minus,
  Plus,
  ShoppingBag,
} from "lucide-react";

import {
  useParams,
  useRouter,
} from "next/navigation";

import {
  BACKEND_URL,
} from "@/lib/api";

import {
  customerApiFetch,
} from "@/lib/customerApi";

import ProductCard, { StoreProductCardData } from "@/components/store/ProductCard";

import {
  storeApiFetch,
} from "@/lib/storeApi";

/* -------------------------------------------------------------------------- */
/* Types                                                                      */
/* -------------------------------------------------------------------------- */

type ProductImage = {
  id: number;
  image: string;

  alt_text:
    | string
    | null;

  is_primary: boolean;
};

type Size = {
  id: number;
  name: string;

  display_name:
    | string
    | null;
};

type Color = {
  id: number;
  name: string;

  display_name:
    | string
    | null;

  hex_code:
    | string
    | null;
};

type Inventory = {
  quantity: number;
  reserved_quantity: number;
};

type Variant = {
  id: number;

  size_id: number;
  color_id: number;

  sku: string;

  mrp: string;
  selling_price: string;

  status: string;

  size:
    | Size
    | null;

  color:
    | Color
    | null;

  inventory:
    | Inventory
    | null;
};

type Review = {
  id: number;
  rating: number;
  title: string | null;
  comment: string | null;
  status: string;
  created_at: string;
  user?: { id: number; name: string };
};

type Product = {
  id: number;

  name: string;
  slug: string;

  short_description:
    | string
    | null;

  description:
    | string
    | null;

  mrp: string;
  selling_price: string;

  set_quantity: number;

  featured: boolean;
  best_seller: boolean;
  new_arrival: boolean;

  category:
    | {
        id: number;
        name: string;
        slug: string;
      }
    | null;

  images: ProductImage[];

  variants: Variant[];
  reviews?: Review[];
  review_count?: number;
  review_average?: number;
};

/* -------------------------------------------------------------------------- */
/* Page                                                                       */
/* -------------------------------------------------------------------------- */

export default function ProductDetailPage() {
  const params =
    useParams();

  const router =
    useRouter();

  const slug =
    String(
      params.slug
    );

  /* ------------------------------------------------------------------------ */
  /* Product                                                                  */
  /* ------------------------------------------------------------------------ */

  const [
    product,
    setProduct,
  ] = useState<Product | null>(
    null
  );

  const [
    loading,
    setLoading,
  ] = useState(true);

  const [
    error,
    setError,
  ] = useState("");

  const [recommended, setRecommended] = useState<StoreProductCardData[]>([]);

  /* ------------------------------------------------------------------------ */
  /* Gallery                                                                  */
  /* ------------------------------------------------------------------------ */

  const [
    selectedImage,
    setSelectedImage,
  ] = useState(0);

  /* ------------------------------------------------------------------------ */
  /* Variant                                                                  */
  /* ------------------------------------------------------------------------ */

  const [
    selectedSizeId,
    setSelectedSizeId,
  ] = useState<number | null>(
    null
  );

  const [
    selectedColorId,
    setSelectedColorId,
  ] = useState<number | null>(
    null
  );

  const [
    quantity,
    setQuantity,
  ] = useState(1);

  /* ------------------------------------------------------------------------ */
  /* Cart                                                                     */
  /* ------------------------------------------------------------------------ */

  const [
    adding,
    setAdding,
  ] = useState(false);

  const [
    cartMessage,
    setCartMessage,
  ] = useState("");

  /* ------------------------------------------------------------------------ */
  /* Wishlist                                                                 */
  /* ------------------------------------------------------------------------ */

  const [
    wishlisted,
    setWishlisted,
  ] = useState(false);

  const [
    wishlistItemId,
    setWishlistItemId,
  ] = useState<number | null>(
    null
  );

  const [
    wishlistLoading,
    setWishlistLoading,
  ] = useState(false);

  const [reviewRating, setReviewRating] = useState(5);
  const [reviewTitle, setReviewTitle] = useState("");
  const [reviewComment, setReviewComment] = useState("");
  const [reviewSubmitting, setReviewSubmitting] = useState(false);
  const [reviewMessage, setReviewMessage] = useState("");
  const [myReview, setMyReview] = useState<Review | null>(null);

  /* ------------------------------------------------------------------------ */
  /* Load Product                                                             */
  /* ------------------------------------------------------------------------ */

  const loadProduct =
    useCallback(async () => {
      try {
        setLoading(true);
        setError("");

        const response =
          await storeApiFetch(
            `/store/products/${slug}`
          );

        const data =
          await response.json();

        if (!response.ok) {
          setError(
            data.message ||
              "Product not found."
          );

          return;
        }

        const item =
          data.data as Product;

        setProduct(item);
        setRecommended(Array.isArray(data?.recommended) ? data.recommended : []);

        setSelectedImage(0);

        /*
        |--------------------------------------------------------------------------
        | Select First Available Variant
        |--------------------------------------------------------------------------
        */

        const firstAvailable =
          item.variants.find(
            (variant) => {
              if (
                variant.status !==
                "active"
              ) {
                return false;
              }

              if (
                !variant.inventory
              ) {
                return false;
              }

              const available =
                variant.inventory
                  .quantity -
                variant.inventory
                  .reserved_quantity;

              return available > 0;
            }
          );

        /*
        |--------------------------------------------------------------------------
        | If all are out of stock,
        | still select first variant.
        |--------------------------------------------------------------------------
        */

        const initialVariant =
          firstAvailable ||
          item.variants[0];

        if (initialVariant) {
          setSelectedSizeId(
            initialVariant.size_id
          );

          setSelectedColorId(
            initialVariant.color_id
          );
        } else {
          setSelectedSizeId(null);
          setSelectedColorId(null);
        }

        setQuantity(1);
      } catch {
        setError(
          "Unable to connect to server."
        );
      } finally {
        setLoading(false);
      }
    }, [slug]);

  useEffect(() => {
    loadProduct();
  }, [loadProduct]);

  /* ------------------------------------------------------------------------ */
  /* Check Wishlist                                                           */
  /* ------------------------------------------------------------------------ */

  useEffect(() => {
    if (!product) {
      return;
    }

    const token =
      localStorage.getItem(
        "customer_token"
      );

    if (!token) {
      setWishlisted(false);
      setWishlistItemId(null);

      return;
    }

    async function checkWishlist() {
      try {
        const response =
          await customerApiFetch(
            `/customer/wishlist/check/${product!.id}`
          );

        const data =
          await response.json();

        if (!response.ok) {
          return;
        }

        setWishlisted(
          Boolean(
            data.data
              ?.wishlisted
          )
        );

        setWishlistItemId(
          data.data
            ?.wishlist_item_id
            ? Number(
                data.data
                  .wishlist_item_id
              )
            : null
        );
      } catch {
        // Wishlist failure should not block product page.
      }
    }

    checkWishlist();
  }, [product]);

  /* ------------------------------------------------------------------------ */
  useEffect(() => {
    if (!product) return;
    const token = localStorage.getItem("customer_token");
    if (!token) return;
    async function loadMyReview() {
      try {
        const response = await customerApiFetch(`/customer/products/${product!.id}/reviews/mine`);
        if (!response.ok) return;
        const json = await response.json();
        if (json?.data) {
          const review = json.data as Review;
          setMyReview(review);
          setReviewRating(review.rating);
          setReviewTitle(review.title || "");
          setReviewComment(review.comment || "");
        }
      } catch {
        // Ignore review lookup failures.
      }
    }
    void loadMyReview();
  }, [product]);

  async function submitReview() {
    if (!product) return;
    if (!localStorage.getItem("customer_token")) {
      router.push(`/login?redirect=/product/${product.slug}`);
      return;
    }
    try {
      setReviewSubmitting(true);
      setReviewMessage("");
      const response = await customerApiFetch(`/customer/products/${product.id}/reviews`, {
        method: "POST",
        body: JSON.stringify({ rating: reviewRating, title: reviewTitle.trim() || null, comment: reviewComment.trim() || null }),
      });
      const json = await response.json();
      if (!response.ok) {
        setReviewMessage(json?.message || "Unable to submit your review.");
        return;
      }
      setReviewMessage(json?.message || "Review submitted for approval.");
      setMyReview(json?.data || null);
    } catch {
      setReviewMessage("Unable to connect to the server.");
    } finally {
      setReviewSubmitting(false);
    }
  }

  /* Sizes                                                                    */
  /* ------------------------------------------------------------------------ */

  const sizes =
    useMemo(() => {
      if (!product) {
        return [];
      }

      const map =
        new Map<
          number,
          Size
        >();

      product.variants.forEach(
        (variant) => {
          if (variant.size) {
            map.set(
              variant.size.id,
              variant.size
            );
          }
        }
      );

      return Array.from(
        map.values()
      );
    }, [product]);

  /* ------------------------------------------------------------------------ */
  /* Colors For Selected Size                                                 */
  /* ------------------------------------------------------------------------ */

  const colors =
    useMemo(() => {
      if (
        !product ||
        !selectedSizeId
      ) {
        return [];
      }

      const map =
        new Map<
          number,
          Color
        >();

      product.variants
        .filter(
          (variant) =>
            variant.size_id ===
            selectedSizeId
        )
        .forEach(
          (variant) => {
            if (variant.color) {
              map.set(
                variant.color.id,
                variant.color
              );
            }
          }
        );

      return Array.from(
        map.values()
      );
    }, [
      product,
      selectedSizeId,
    ]);

  /* ------------------------------------------------------------------------ */
  /* Selected Variant                                                         */
  /* ------------------------------------------------------------------------ */

  const selectedVariant =
    useMemo(() => {
      if (
        !product ||
        !selectedSizeId ||
        !selectedColorId
      ) {
        return null;
      }

      return (
        product.variants.find(
          (variant) =>
            variant.size_id ===
              selectedSizeId &&
            variant.color_id ===
              selectedColorId
        ) || null
      );
    }, [
      product,
      selectedSizeId,
      selectedColorId,
    ]);

  /* ------------------------------------------------------------------------ */
  /* Available Quantity                                                       */
  /* ------------------------------------------------------------------------ */

  const availableQuantity =
    useMemo(() => {
      if (
        !selectedVariant ||
        !selectedVariant.inventory
      ) {
        return 0;
      }

      return Math.max(
        0,

        selectedVariant
          .inventory.quantity -

          selectedVariant
            .inventory
            .reserved_quantity
      );
    }, [
      selectedVariant,
    ]);

  /* ------------------------------------------------------------------------ */
  /* Size Availability                                                        */
  /* ------------------------------------------------------------------------ */

  function sizeHasStock(
    sizeId: number
  ) {
    if (!product) {
      return false;
    }

    return product.variants.some(
      (variant) => {
        if (
          variant.size_id !==
            sizeId ||
          variant.status !==
            "active" ||
          !variant.inventory
        ) {
          return false;
        }

        return (
          variant.inventory.quantity -
            variant.inventory
              .reserved_quantity >
          0
        );
      }
    );
  }

  /* ------------------------------------------------------------------------ */
  /* Color Stock                                                              */
  /* ------------------------------------------------------------------------ */

  function colorHasStock(
    colorId: number
  ) {
    if (
      !product ||
      !selectedSizeId
    ) {
      return false;
    }

    const variant =
      product.variants.find(
        (item) =>
          item.size_id ===
            selectedSizeId &&
          item.color_id ===
            colorId
      );

    if (
      !variant ||
      variant.status !==
        "active" ||
      !variant.inventory
    ) {
      return false;
    }

    return (
      variant.inventory.quantity -
        variant.inventory
          .reserved_quantity >
      0
    );
  }

  /* ------------------------------------------------------------------------ */
  /* Select Size                                                              */
  /* ------------------------------------------------------------------------ */

  function selectSize(
    sizeId: number
  ) {
    if (!product) {
      return;
    }

    setSelectedSizeId(
      sizeId
    );

    setQuantity(1);
    setCartMessage("");

    /*
    |--------------------------------------------------------------------------
    | Prefer first in-stock color
    |--------------------------------------------------------------------------
    */

    const sizeVariants =
      product.variants.filter(
        (variant) =>
          variant.size_id ===
          sizeId
      );

    const availableVariant =
      sizeVariants.find(
        (variant) => {
          if (
            variant.status !==
              "active" ||
            !variant.inventory
          ) {
            return false;
          }

          return (
            variant.inventory
              .quantity -
              variant.inventory
                .reserved_quantity >
            0
          );
        }
      );

    const initialVariant =
      availableVariant ||
      sizeVariants[0];

    setSelectedColorId(
      initialVariant
        ?.color_id ??
        null
    );
  }

  /* ------------------------------------------------------------------------ */
  /* Select Color                                                             */
  /* ------------------------------------------------------------------------ */

  function selectColor(
    colorId: number
  ) {
    setSelectedColorId(
      colorId
    );

    setQuantity(1);
    setCartMessage("");
  }

  /* ------------------------------------------------------------------------ */
  /* Toggle Wishlist                                                          */
  /* ------------------------------------------------------------------------ */

  async function toggleWishlist() {
    if (!product) {
      return;
    }

    const token =
      localStorage.getItem(
        "customer_token"
      );

    if (!token) {
      router.push(
        "/login"
      );

      return;
    }

    try {
      setWishlistLoading(
        true
      );

      /*
      |--------------------------------------------------------------------------
      | Remove
      |--------------------------------------------------------------------------
      */

      if (
        wishlisted &&
        wishlistItemId
      ) {
        const response =
          await customerApiFetch(
            `/customer/wishlist/${wishlistItemId}`,
            {
              method:
                "DELETE",
            }
          );

        const data =
          await response.json();

        if (!response.ok) {
          window.alert(
            data.message ||
              "Unable to remove product from wishlist."
          );

          return;
        }

        setWishlisted(
          false
        );
        window.dispatchEvent(new CustomEvent("banglesmart:customer-refresh", { detail: { wishlistDelta: -1 } }));

        setWishlistItemId(
          null
        );

        return;
      }

      /*
      |--------------------------------------------------------------------------
      | Add
      |--------------------------------------------------------------------------
      */

      const response =
        await customerApiFetch(
          "/customer/wishlist",
          {
            method: "POST",

            body: JSON.stringify({
              product_id:
                product.id,
            }),
          }
        );

      const data =
        await response.json();

      if (!response.ok) {
        window.alert(
          data.message ||
            "Unable to add product to wishlist."
        );

        return;
      }

      setWishlisted(
        true
      );
      window.dispatchEvent(new CustomEvent("banglesmart:customer-refresh", { detail: { wishlistDelta: 1 } }));

      setWishlistItemId(
        data.data?.id
          ? Number(
              data.data.id
            )
          : null
      );
    } catch {
      window.alert(
        "Unable to connect to server."
      );
    } finally {
      setWishlistLoading(
        false
      );
    }
  }

  /* ------------------------------------------------------------------------ */
  /* Add To Cart                                                              */
  /* ------------------------------------------------------------------------ */

  async function addToCart() {
    if (!selectedVariant) {
      window.alert(
        "Please select a size and color."
      );

      return;
    }

    if (
      selectedVariant.status !==
      "active"
    ) {
      window.alert(
        "This variant is currently unavailable."
      );

      return;
    }

    if (
      availableQuantity <= 0
    ) {
      window.alert(
        "This variant is out of stock."
      );

      return;
    }

    if (
      quantity >
      availableQuantity
    ) {
      window.alert(
        `Only ${availableQuantity} item(s) are available.`
      );

      return;
    }

    const token =
      localStorage.getItem(
        "customer_token"
      );

    if (!token) {
      router.push(
        "/login"
      );

      return;
    }

    try {
      setAdding(true);
      setCartMessage("");

      const response =
        await customerApiFetch(
          "/customer/cart/items",
          {
            method: "POST",

            body: JSON.stringify({
              product_variant_id:
                selectedVariant.id,

              quantity,
            }),
          }
        );

      const data =
        await response.json();

      if (!response.ok) {
        window.alert(
          data.message ||
            "Unable to add product to cart."
        );

        return;
      }

      setCartMessage(
        "Added to cart successfully."
      );
      window.dispatchEvent(new CustomEvent("banglesmart:customer-refresh", { detail: { cartCount: Number(data?.data?.item_count || 0) } }));
    } catch {
      window.alert(
        "Unable to connect to server."
      );
    } finally {
      setAdding(false);
    }
  }

  /* ------------------------------------------------------------------------ */
  /* Price                                                                    */
  /* ------------------------------------------------------------------------ */

  function formatPrice(
    value:
      | string
      | number
  ) {
    return new Intl.NumberFormat(
      "en-IN",
      {
        style: "currency",
        currency: "INR",
        maximumFractionDigits: 0,
      }
    ).format(
      Number(value)
    );
  }

  /* ------------------------------------------------------------------------ */
  /* Loading                                                                  */
  /* ------------------------------------------------------------------------ */

  if (loading) {
    return (
      <main className="flex min-h-screen items-center justify-center bg-gray-50">

        <p className="text-sm text-gray-500">
          Loading product...
        </p>

      </main>
    );
  }

  /* ------------------------------------------------------------------------ */
  /* Error                                                                    */
  /* ------------------------------------------------------------------------ */

  if (
    error ||
    !product
  ) {
    return (
      <main className="min-h-screen bg-gray-50 px-4 py-10">

        <div className="mx-auto max-w-7xl">

          <Link
            href="/shop"
            className="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-gray-900"
          >
            <ArrowLeft
              size={17}
            />

            Back to Shop
          </Link>

          <div className="mt-6 rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-700">
            {error ||
              "Product not found."}
          </div>

        </div>

      </main>
    );
  }

  /* ------------------------------------------------------------------------ */
  /* Display Values                                                           */
  /* ------------------------------------------------------------------------ */

  const currentImage =
    product.images[
      selectedImage
    ];

  const price =
    selectedVariant
      ?.selling_price ||
    product.selling_price;

  const mrp =
    selectedVariant
      ?.mrp ||
    product.mrp;

  /* ------------------------------------------------------------------------ */
  /* UI                                                                       */
  /* ------------------------------------------------------------------------ */

  return (
    <main className="min-h-screen bg-gray-50 px-4 py-8">

      <div className="mx-auto max-w-7xl">

        {/* Back */}

        <Link
          href="/shop"
          className="mb-6 inline-flex items-center gap-2 text-sm font-medium text-gray-600 transition hover:text-gray-900"
        >
          <ArrowLeft
            size={17}
          />

          Back to Shop
        </Link>

        {/* Main Grid */}

        <div className="grid grid-cols-1 gap-8 lg:grid-cols-2">

          {/* --------------------------------------------------------------- */}
          {/* Gallery                                                         */}
          {/* --------------------------------------------------------------- */}

          <div>

            {/* Main Image */}

            <div className="aspect-square overflow-hidden rounded-2xl border border-gray-200 bg-white">

              {currentImage ? (

                <img
                  src={`${BACKEND_URL}/storage/${currentImage.image}`}
                  alt={
                    currentImage
                      .alt_text ||
                    product.name
                  }
                  className="h-full w-full object-cover"
                />

              ) : (

                <div className="flex h-full items-center justify-center">

                  <ShoppingBag
                    size={50}
                    className="text-gray-300"
                  />

                </div>

              )}

            </div>

            {/* Thumbnails */}

            {product.images.length >
              1 && (

              <div className="mt-4 grid grid-cols-5 gap-3">

                {product.images.map(
                  (
                    image,
                    index
                  ) => (

                    <button
                      key={
                        image.id
                      }
                      type="button"
                      onClick={() =>
                        setSelectedImage(
                          index
                        )
                      }
                      className={`aspect-square overflow-hidden rounded-lg border-2 bg-white transition ${
                        selectedImage ===
                        index
                          ? "border-gray-900"
                          : "border-transparent hover:border-gray-300"
                      }`}
                    >

                      <img
                        src={`${BACKEND_URL}/storage/${image.image}`}
                        alt={
                          image.alt_text ||
                          product.name
                        }
                        className="h-full w-full object-cover"
                      />

                    </button>

                  )
                )}

              </div>

            )}

          </div>

          {/* --------------------------------------------------------------- */}
          {/* Product Details                                                 */}
          {/* --------------------------------------------------------------- */}

          <div className="rounded-2xl border border-gray-200 bg-white p-6 sm:p-8">

            {/* Category */}

            {product.category && (

              <Link
                href={`/shop?category=${product.category.slug}`}
                className="text-sm font-medium text-gray-500 transition hover:text-gray-900"
              >
                {
                  product.category
                    .name
                }
              </Link>

            )}

            {/* Title */}

            <h1 className="mt-2 text-3xl font-semibold leading-tight text-gray-900">
              {product.name}
            </h1>

            {/* Badges */}

            <div className="mt-3 flex flex-wrap gap-2">

              {product.new_arrival && (
                <span className="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                  New Arrival
                </span>
              )}

              {product.best_seller && (
                <span className="rounded-full bg-yellow-50 px-2.5 py-1 text-xs font-medium text-yellow-700">
                  Best Seller
                </span>
              )}

              {product.featured && (
                <span className="rounded-full bg-purple-50 px-2.5 py-1 text-xs font-medium text-purple-700">
                  Featured
                </span>
              )}

            </div>

            {/* Short Description */}

            {product.short_description && (

              <p className="mt-4 leading-7 text-gray-600">
                {
                  product.short_description
                }
              </p>

            )}

            {/* Price */}

            <div className="mt-6 flex flex-wrap items-center gap-3">

              <span className="text-3xl font-semibold text-gray-900">
                {formatPrice(
                  price
                )}
              </span>

              {Number(mrp) >
                Number(price) && (

                <span className="text-lg text-gray-400 line-through">
                  {formatPrice(
                    mrp
                  )}
                </span>

              )}

              {Number(mrp) >
                Number(price) &&
                Number(mrp) >
                  0 && (

                <span className="rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700">

                  {Math.round(
                    ((Number(mrp) -
                      Number(price)) /
                      Number(mrp)) *
                      100
                  )}
                  % OFF

                </span>

              )}

            </div>

            {/* Wishlist */}

            <button
              type="button"
              disabled={
                wishlistLoading
              }
              onClick={
                toggleWishlist
              }
              className={`mt-5 flex items-center cursor-pointer gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-50 ${
                wishlisted
                  ? "border-red-200 bg-red-50 text-red-600"
                  : "border-gray-300 bg-white text-gray-700 hover:bg-gray-50"
              }`}
            >

              <Heart
                size={18}
                fill={
                  wishlisted
                    ? "currentColor"
                    : "none"
                }
              />

              {wishlistLoading
                ? "Please wait..."
                : wishlisted
                ? "Saved to Wishlist"
                : "Add to Wishlist"}

            </button>

            {/* Divider */}

            <div className="my-7 border-t border-gray-200" />

            {/* ------------------------------------------------------------- */}
            {/* Size                                                          */}
            {/* ------------------------------------------------------------- */}

            <div>

              <div className="mb-3 flex flex-wrap items-center justify-between gap-2">

                <h2 className="text-sm font-semibold text-gray-900">
                  Select Size
                </h2>

                {selectedVariant && (

                  <span className="font-mono text-xs text-gray-400">
                    SKU:{" "}
                    {
                      selectedVariant.sku
                    }
                  </span>

                )}

              </div>

              {sizes.length ===
              0 ? (

                <p className="text-sm text-gray-500">
                  No sizes available.
                </p>

              ) : (

                <div className="flex flex-wrap gap-2">

                  {sizes.map(
                    (size) => {

                      const hasStock =
                        sizeHasStock(
                          size.id
                        );

                      const selected =
                        selectedSizeId ===
                        size.id;

                      return (

                        <button
                          key={
                            size.id
                          }
                          type="button"
                          onClick={() =>
                            selectSize(
                              size.id
                            )
                          }
                          className={`relative min-w-16 cursor-pointer rounded-lg border px-4 py-2.5 text-sm font-medium transition ${
                            selected
                              ? "border-gray-900 bg-gray-900 text-white"
                              : hasStock
                              ? "border-gray-300 bg-white text-gray-700 hover:border-gray-500"
                              : "border-gray-200 bg-gray-50 text-gray-400"
                          }`}
                        >

                          {size.display_name ||
                            size.name}

                        </button>

                      );
                    }
                  )}

                </div>

              )}

            </div>

            {/* ------------------------------------------------------------- */}
            {/* Color                                                         */}
            {/* ------------------------------------------------------------- */}

            <div className="mt-7">

              <h2 className="mb-3 text-sm font-semibold text-gray-900">
                Select Color
              </h2>

              {colors.length ===
              0 ? (

                <p className="text-sm text-gray-500">
                  No colors available for this size.
                </p>

              ) : (

                <div className="flex flex-wrap gap-3">

                  {colors.map(
                    (color) => {

                      const selected =
                        selectedColorId ===
                        color.id;

                      const hasStock =
                        colorHasStock(
                          color.id
                        );

                      return (

                        <button
                          key={
                            color.id
                          }
                          type="button"
                          onClick={() =>
                            selectColor(
                              color.id
                            )
                          }
                          className={`flex items-center cursor-pointer gap-2 rounded-lg border px-3 py-2.5 text-sm transition ${
                            selected
                              ? "border-gray-900 bg-gray-50"
                              : hasStock
                              ? "border-gray-300 bg-white hover:border-gray-500"
                              : "border-gray-200 bg-gray-50 text-gray-400"
                          }`}
                        >

                          <span
                            className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-gray-300"
                            style={{
                              backgroundColor:
                                color.hex_code ||
                                "#ffffff",
                            }}
                          >

                            {selected && (

                              <Check
                                size={13}
                                className="text-white drop-shadow"
                              />

                            )}

                          </span>

                          <span>

                            {color.display_name ||
                              color.name}

                            {!hasStock &&
                              " - Out"}

                          </span>

                        </button>

                      );
                    }
                  )}

                </div>

              )}

            </div>

            {/* ------------------------------------------------------------- */}
            {/* Stock                                                         */}
            {/* ------------------------------------------------------------- */}

            <div className="mt-6">

              {!selectedVariant ? (

                <p className="text-sm text-gray-500">
                  Select a size and color.
                </p>

              ) : availableQuantity >
                0 ? (

                <div>

                  <p className="text-sm font-medium text-green-700">
                    In Stock
                  </p>

                  <p className="mt-1 text-xs text-gray-500">
                    {
                      availableQuantity
                    }{" "}
                    available
                  </p>

                </div>

              ) : (

                <p className="text-sm font-medium text-red-600">
                  Out of Stock
                </p>

              )}

            </div>

            {/* ------------------------------------------------------------- */}
            {/* Quantity + Cart                                               */}
            {/* ------------------------------------------------------------- */}

            <div className="mt-7 flex flex-col gap-3 sm:flex-row">

              {/* Quantity */}

              <div className="flex h-12 items-center rounded-lg border border-gray-300">

                <button
                  type="button"
                  disabled={
                    quantity <= 1
                  }
                  onClick={() =>
                    setQuantity(
                      Math.max(
                        1,
                        quantity - 1
                      )
                    )
                  }
                  className="flex h-full w-11 items-center justify-center text-gray-600 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-30"
                >
                  <Minus
                    size={16}
                  />
                </button>

                <span className="min-w-12 text-center text-sm font-semibold text-gray-900">
                  {quantity}
                </span>

                <button
                  type="button"
                  disabled={
                    availableQuantity <=
                      0 ||
                    quantity >=
                      availableQuantity
                  }
                  onClick={() =>
                    setQuantity(
                      Math.min(
                        availableQuantity,
                        quantity + 1
                      )
                    )
                  }
                  className="flex h-full w-11 items-center justify-center text-gray-600 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-30"
                >
                  <Plus
                    size={16}
                  />
                </button>

              </div>

              {/* Add to Cart */}

              <button
                type="button"
                disabled={
                  adding ||
                  !selectedVariant ||
                  selectedVariant.status !==
                    "active" ||
                  availableQuantity <=
                    0
                }
                onClick={
                  addToCart
                }
                className="flex h-12 flex-1 items-center justify-center gap-2 rounded-lg bg-gray-900 px-6 text-sm font-medium text-white transition hover:bg-black disabled:cursor-not-allowed disabled:opacity-40 cursor-pointer"
              >

                <ShoppingBag
                  size={18}
                />

                {adding
                  ? "Adding..."
                  : "Add to Cart"}

              </button>

            </div>

            {/* Cart Success */}

            {cartMessage && (

              <div className="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3">

                <span className="text-sm font-medium text-green-700">
                  {cartMessage}
                </span>

                <Link
                  href="/cart"
                  className="text-sm font-semibold text-green-800 underline"
                >
                  View Cart
                </Link>

              </div>

            )}

            {/* ------------------------------------------------------------- */}
            {/* Product Details                                               */}
            {/* ------------------------------------------------------------- */}

            {product.description && (

              <div className="mt-8 border-t border-gray-200 pt-7">

                <h2 className="font-semibold text-gray-900">
                  Product Details
                </h2>

                <p className="mt-3 whitespace-pre-line text-sm leading-7 text-gray-600">
                  {
                    product.description
                  }
                </p>

              </div>

            )}

            {/* Set Quantity */}

            {product.set_quantity >
              1 && (

              <div className="mt-6 rounded-lg bg-gray-50 px-4 py-3">

                <p className="text-sm text-gray-600">

                  This product contains{" "}

                  <span className="font-semibold text-gray-900">
                    {
                      product.set_quantity
                    }
                  </span>{" "}

                  pieces per set.

                </p>

              </div>

            )}

          </div>

        </div>

      </div>

      <section className="mx-auto mt-12 max-w-7xl border-t border-[#e9e2d8] px-1 pt-10">
        <div className="grid gap-10 lg:grid-cols-[300px_minmax(0,1fr)]">
          <div className="rounded-3xl bg-[#f6efe4] p-7">
            <p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#8f0828]">Customer love</p>
            <h2 className="mt-2 font-[family-name:var(--font-playfair)] text-3xl text-[#191919]">Reviews</h2>
            <div className="mt-6 flex items-end gap-3"><span className="font-[family-name:var(--font-playfair)] text-5xl text-[#191919]">{product.review_average || "0.0"}</span><div className="pb-1"><div className="text-[#c9a227]">{"★".repeat(Math.round(product.review_average || 0))}{"☆".repeat(Math.max(0, 5 - Math.round(product.review_average || 0)))}</div><p className="mt-1 text-xs text-[#777]">{product.review_count || 0} verified review{product.review_count === 1 ? "" : "s"}</p></div></div>
            <p className="mt-5 text-sm leading-6 text-[#6d675e]">Real feedback from customers who have received their BanglesMart order.</p>
          </div>
          <div>
            {product.reviews && product.reviews.length > 0 ? <div className="space-y-4">{product.reviews.map((review) => <article key={review.id} className="rounded-2xl border border-[#e7dfd4] bg-white p-6"><div className="flex flex-wrap items-center justify-between gap-3"><div><div className="text-sm tracking-wide text-[#c9a227]">{"★".repeat(review.rating)}{"☆".repeat(5 - review.rating)}</div><h3 className="mt-2 text-sm font-semibold text-[#191919]">{review.title || "Beautiful purchase"}</h3></div><span className="text-xs text-[#8a8379]">{review.user?.name || "Verified Customer"}</span></div>{review.comment && <p className="mt-4 text-sm leading-7 text-[#666]">{review.comment}</p>}</article>)}</div> : <div className="rounded-2xl border border-dashed border-[#d9d0c2] bg-white p-8 text-center"><p className="font-[family-name:var(--font-playfair)] text-xl">Be the first to share your experience.</p><p className="mt-2 text-sm text-[#777]">Reviews become available after a delivered purchase.</p></div>}
            <div className="mt-6 rounded-2xl border border-[#e7dfd4] bg-white p-6">
              <div className="flex flex-wrap items-center justify-between gap-2"><div><h3 className="text-sm font-semibold">Write a review</h3><p className="mt-1 text-xs text-[#888]">Only delivered purchases can submit reviews.</p></div>{myReview && <span className="rounded-full bg-[#f8f1e5] px-3 py-1 text-[10px] font-semibold uppercase tracking-wider text-[#8f0828]">{myReview.status === "approved" ? "Published" : "Pending approval"}</span>}</div>
              <div className="mt-5 flex gap-1" aria-label="Rating">{[1,2,3,4,5].map((star) => <button key={star} type="button" onClick={() => setReviewRating(star)} aria-label={`${star} star${star > 1 ? "s" : ""}`} className={`text-2xl ${star <= reviewRating ? "text-[#c9a227]" : "text-[#d8d2c8]"}`}>★</button>)}</div>
              <div className="mt-4 grid gap-3 sm:grid-cols-2"><input value={reviewTitle} onChange={(e) => setReviewTitle(e.target.value)} maxLength={150} placeholder="Review title" className="rounded-xl border border-[#ddd4c7] px-4 py-3 text-sm outline-none focus:border-[#c9a227]" /><textarea value={reviewComment} onChange={(e) => setReviewComment(e.target.value)} maxLength={2000} rows={3} placeholder="Tell us about the product..." className="sm:col-span-2 rounded-xl border border-[#ddd4c7] px-4 py-3 text-sm outline-none focus:border-[#c9a227]" /></div>
              {reviewMessage && <p className="mt-3 rounded-xl bg-[#f8f1e5] px-4 py-3 text-xs text-[#6d675e]">{reviewMessage}</p>}
              <button type="button" disabled={reviewSubmitting} onClick={submitReview} className="mt-4 rounded-full bg-[#111827] px-6 py-3 text-xs font-semibold text-white transition hover:bg-black disabled:opacity-50">{reviewSubmitting ? "Submitting..." : myReview ? "Update Review" : "Submit Review"}</button>
            </div>
          </div>
        </div>
      </section>

      {recommended.length > 0 && (
        <section className="mx-auto mt-16 max-w-7xl border-t border-[#e9e2d8] px-1 pt-12">
          <div className="flex flex-col items-center justify-between gap-3 text-center sm:flex-row sm:text-left">
            <div>
              <p className="text-[10px] font-semibold uppercase tracking-[0.22em] text-[#c9a227]">You may also love</p>
              <h2 className="mt-2 font-[family-name:var(--font-playfair)] text-3xl text-[#191919]">Recommended for you</h2>
              <p className="mt-1 text-sm text-[#777]">More pieces from this collection.</p>
            </div>
            <Link href="/shop" className="text-sm font-semibold text-[#8f0828]">Explore the collection →</Link>
          </div>
          <div className="mt-8 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
            {recommended.map((item) => <ProductCard key={item.id} product={item} />)}
          </div>
        </section>
      )}

    </main>
  );
}