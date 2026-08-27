"use client";

import {
  FormEvent,
  useCallback,
  useEffect,
  useState,
} from "react";

import {
  Pencil,
  Plus,
  Trash2,
  X,
} from "lucide-react";

import { apiFetch } from "@/lib/api";


type Size = {
  id: number;
  name: string;
  display_name: string | null;
  status: string;
};


type Color = {
  id: number;
  name: string;
  display_name: string | null;
  hex_code: string | null;
  status: string;
};


type Inventory = {
  id: number;
  quantity: number;
  reserved_quantity: number;
  low_stock_limit: number;
};


type Variant = {
  id: number;

  product_id: number;

  size_id: number;
  color_id: number;

  sku: string;

  mrp: string;
  selling_price: string;

  status: string;

  size: Size;
  color: Color;

  inventory: Inventory | null;
};


type Props = {
  productId: string;
};


export default function ProductVariants({
  productId,
}: Props) {
  const [variants, setVariants] =
    useState<Variant[]>([]);

  const [sizes, setSizes] =
    useState<Size[]>([]);

  const [colors, setColors] =
    useState<Color[]>([]);

  const [loading, setLoading] =
    useState(true);

  const [showForm, setShowForm] =
    useState(false);

  const [editing, setEditing] =
    useState<Variant | null>(null);


  /*
  |--------------------------------------------------------------------------
  | Form
  |--------------------------------------------------------------------------
  */

  const [sizeId, setSizeId] =
    useState("");

  const [colorId, setColorId] =
    useState("");

  const [sku, setSku] =
    useState("");

  const [mrp, setMrp] =
    useState("");

  const [sellingPrice, setSellingPrice] =
    useState("");

  const [quantity, setQuantity] =
    useState("0");

  const [
    lowStockLimit,
    setLowStockLimit,
  ] = useState("5");

  const [status, setStatus] =
    useState("active");

  const [saving, setSaving] =
    useState(false);

  const [error, setError] =
    useState("");


  /*
  |--------------------------------------------------------------------------
  | Load Data
  |--------------------------------------------------------------------------
  */

  const loadData =
    useCallback(async () => {
      try {
        setLoading(true);

        const [
          variantsResponse,
          sizesResponse,
          colorsResponse,
        ] = await Promise.all([
          apiFetch(
            `/admin/products/${productId}/variants`
          ),

          apiFetch("/admin/sizes"),

          apiFetch("/admin/colors"),
        ]);

        const variantsData =
          await variantsResponse.json();

        const sizesData =
          await sizesResponse.json();

        const colorsData =
          await colorsResponse.json();


        if (variantsResponse.ok) {
          setVariants(
            variantsData.data || []
          );
        }


        if (sizesResponse.ok) {
          setSizes(
            (sizesData.data || []).filter(
              (size: Size) =>
                size.status === "active"
            )
          );
        }


        if (colorsResponse.ok) {
          setColors(
            (colorsData.data || []).filter(
              (color: Color) =>
                color.status === "active"
            )
          );
        }

      } finally {
        setLoading(false);
      }

    }, [productId]);


  useEffect(() => {
    loadData();
  }, [loadData]);


  /*
  |--------------------------------------------------------------------------
  | Reset
  |--------------------------------------------------------------------------
  */

  function resetForm() {
    setSizeId("");
    setColorId("");

    setSku("");
    setMrp("");
    setSellingPrice("");

    setQuantity("0");
    setLowStockLimit("5");

    setStatus("active");

    setEditing(null);
    setError("");
  }


  function openCreate() {
    resetForm();
    setShowForm(true);
  }


  function openEdit(
    variant: Variant
  ) {
    setEditing(variant);

    setSizeId(
      String(variant.size_id)
    );

    setColorId(
      String(variant.color_id)
    );

    setSku(variant.sku);

    setMrp(
      String(variant.mrp)
    );

    setSellingPrice(
      String(
        variant.selling_price
      )
    );

    setQuantity(
      String(
        variant.inventory
          ?.quantity ?? 0
      )
    );

    setLowStockLimit(
      String(
        variant.inventory
          ?.low_stock_limit ?? 5
      )
    );

    setStatus(
      variant.status
    );

    setError("");

    setShowForm(true);
  }


  function closeForm() {
    setShowForm(false);

    resetForm();
  }


  /*
  |--------------------------------------------------------------------------
  | Submit
  |--------------------------------------------------------------------------
  */

  async function handleSubmit(
    event: FormEvent<HTMLFormElement>
  ) {
    event.preventDefault();

    setSaving(true);
    setError("");

    try {
      const endpoint =
        editing
          ? `/admin/product-variants/${editing.id}`
          : `/admin/products/${productId}/variants`;


      const response =
        await apiFetch(
          endpoint,
          {
            method:
              editing
                ? "PUT"
                : "POST",

            body: JSON.stringify({
              size_id:
                Number(sizeId),

              color_id:
                Number(colorId),

              sku,

              mrp:
                Number(mrp),

              selling_price:
                Number(
                  sellingPrice
                ),

              quantity:
                Number(quantity),

              low_stock_limit:
                Number(
                  lowStockLimit
                ),

              status,
            }),
          }
        );


      const data =
        await response.json();


      if (!response.ok) {
        const errors =
          data.errors as
            | Record<
                string,
                string[]
              >
            | undefined;


        const firstError =
          errors
            ? Object.values(
                errors
              )[0]?.[0]
            : undefined;


        setError(
          firstError ||
            data.message ||
            "Unable to save variant."
        );

        return;
      }


      closeForm();

      await loadData();

    } catch {
      setError(
        "Unable to connect to server."
      );
    } finally {
      setSaving(false);
    }
  }


  /*
  |--------------------------------------------------------------------------
  | Delete
  |--------------------------------------------------------------------------
  */

  async function deleteVariant(
    id: number
  ) {
    if (
      !window.confirm(
        "Delete this variant?"
      )
    ) {
      return;
    }


    const response =
      await apiFetch(
        `/admin/product-variants/${id}`,
        {
          method: "DELETE",
        }
      );


    if (response.ok) {
      await loadData();
    }
  }


  /*
  |--------------------------------------------------------------------------
  | UI
  |--------------------------------------------------------------------------
  */

  return (
    <section className="rounded-xl border border-gray-200 bg-white p-6">

      <div className="flex items-center justify-between">

        <div>
          <h2 className="font-semibold text-gray-900">
            Product Variants
          </h2>

          <p className="mt-1 text-sm text-gray-500">
            Manage size, color,
            pricing and inventory.
          </p>
        </div>


        <button
          type="button"
          onClick={openCreate}
          className="flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-medium text-white"
        >
          <Plus size={18} />

          Add Variant
        </button>

      </div>


      {/* Table */}

      <div className="mt-6 overflow-hidden rounded-xl border border-gray-200">

        <table className="w-full">

          <thead className="bg-gray-50">

            <tr>

              <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                Variant
              </th>

              <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                SKU
              </th>

              <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                Price
              </th>

              <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                Stock
              </th>

              <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
                Status
              </th>

              <th className="px-4 py-3 text-right text-xs font-medium uppercase text-gray-500">
                Actions
              </th>

            </tr>

          </thead>


          <tbody className="divide-y divide-gray-200">

            {loading ? (

              <tr>
                <td
                  colSpan={6}
                  className="px-4 py-10 text-center text-sm text-gray-500"
                >
                  Loading variants...
                </td>
              </tr>

            ) : variants.length === 0 ? (

              <tr>
                <td
                  colSpan={6}
                  className="px-4 py-12 text-center text-sm text-gray-500"
                >
                  No variants added yet.
                </td>
              </tr>

            ) : (

              variants.map(
                (variant) => {

                  const stock =
                    variant.inventory
                      ?.quantity ?? 0;

                  const lowStock =
                    variant.inventory
                      ?.low_stock_limit ??
                    5;


                  return (

                    <tr
                      key={
                        variant.id
                      }
                    >

                      <td className="px-4 py-4">

                        <div className="flex items-center gap-3">

                          <span
                            className="h-6 w-6 rounded-full border border-gray-300"
                            style={{
                              backgroundColor:
                                variant
                                  .color
                                  ?.hex_code ||
                                "#ffffff",
                            }}
                          />

                          <div>

                            <p className="font-medium text-gray-900">
                              {
                                variant
                                  .size
                                  ?.display_name ||
                                variant
                                  .size
                                  ?.name
                              }

                              {" / "}

                              {
                                variant
                                  .color
                                  ?.display_name ||
                                variant
                                  .color
                                  ?.name
                              }
                            </p>

                          </div>

                        </div>

                      </td>


                      <td className="px-4 py-4 text-sm text-gray-600">
                        {
                          variant.sku
                        }
                      </td>


                      <td className="px-4 py-4">

                        <p className="text-sm font-medium text-gray-900">
                          ₹
                          {
                            variant.selling_price
                          }
                        </p>

                        {Number(
                          variant.mrp
                        ) >
                          Number(
                            variant.selling_price
                          ) && (

                          <p className="text-xs text-gray-400 line-through">
                            ₹
                            {
                              variant.mrp
                            }
                          </p>

                        )}

                      </td>


                      <td className="px-4 py-4">

                        <span
                          className={`text-sm font-medium ${
                            stock === 0
                              ? "text-red-600"
                              : stock <=
                                lowStock
                              ? "text-orange-600"
                              : "text-green-700"
                          }`}
                        >
                          {stock}
                        </span>

                      </td>


                      <td className="px-4 py-4">

                        <span
                          className={`rounded-full px-2.5 py-1 text-xs font-medium ${
                            variant.status ===
                            "active"
                              ? "bg-green-50 text-green-700"
                              : "bg-gray-100 text-gray-600"
                          }`}
                        >
                          {
                            variant.status
                          }
                        </span>

                      </td>


                      <td className="px-4 py-4">

                        <div className="flex justify-end gap-2">

                          <button
                            type="button"
                            onClick={() =>
                              openEdit(
                                variant
                              )
                            }
                            className="rounded-lg border border-gray-200 p-2 hover:bg-gray-100"
                          >
                            <Pencil
                              size={16}
                            />
                          </button>


                          <button
                            type="button"
                            onClick={() =>
                              deleteVariant(
                                variant.id
                              )
                            }
                            className="rounded-lg border border-gray-200 p-2 text-red-600 hover:bg-red-50"
                          >
                            <Trash2
                              size={16}
                            />
                          </button>

                        </div>

                      </td>

                    </tr>

                  );

                }
              )

            )}

          </tbody>

        </table>

      </div>


      {/* Drawer */}

      {showForm && (

        <div className="fixed inset-0 z-50 flex justify-end bg-black/30">

          <div className="h-full w-full max-w-lg overflow-y-auto bg-white shadow-xl">

            <div className="flex items-center justify-between border-b border-gray-200 px-6 py-5">

              <div>

                <h2 className="text-lg font-semibold">
                  {editing
                    ? "Edit Variant"
                    : "Add Variant"}
                </h2>

                <p className="mt-1 text-sm text-gray-500">
                  Size, color,
                  pricing and stock.
                </p>

              </div>


              <button
                type="button"
                onClick={closeForm}
                className="rounded-lg p-2 hover:bg-gray-100"
              >
                <X size={20} />
              </button>

            </div>


            <form
              onSubmit={handleSubmit}
              className="space-y-5 p-6"
            >

              {error && (

                <div className="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                  {error}
                </div>

              )}


              {/* Size */}

              <div>

                <label className="mb-2 block text-sm font-medium">
                  Size *
                </label>

                <select
                  required
                  value={sizeId}
                  onChange={(e) =>
                    setSizeId(
                      e.target.value
                    )
                  }
                  className="w-full rounded-lg border border-gray-300 px-4 py-2.5"
                >

                  <option value="">
                    Select Size
                  </option>

                  {sizes.map(
                    (size) => (

                      <option
                        key={size.id}
                        value={size.id}
                      >
                        {size.display_name ||
                          size.name}
                      </option>

                    )
                  )}

                </select>

              </div>


              {/* Color */}

              <div>

                <label className="mb-2 block text-sm font-medium">
                  Color *
                </label>

                <select
                  required
                  value={colorId}
                  onChange={(e) =>
                    setColorId(
                      e.target.value
                    )
                  }
                  className="w-full rounded-lg border border-gray-300 px-4 py-2.5"
                >

                  <option value="">
                    Select Color
                  </option>

                  {colors.map(
                    (color) => (

                      <option
                        key={
                          color.id
                        }
                        value={
                          color.id
                        }
                      >
                        {color.display_name ||
                          color.name}
                      </option>

                    )
                  )}

                </select>

              </div>


              {/* SKU */}

              <div>

                <label className="mb-2 block text-sm font-medium">
                  SKU *
                </label>

                <input
                  required
                  value={sku}
                  onChange={(e) =>
                    setSku(
                      e.target.value
                    )
                  }
                  placeholder="RKB-R-24"
                  className="w-full rounded-lg border border-gray-300 px-4 py-2.5"
                />

              </div>


              {/* Price */}

              <div className="grid grid-cols-2 gap-4">

                <div>

                  <label className="mb-2 block text-sm font-medium">
                    MRP *
                  </label>

                  <input
                    type="number"
                    required
                    min="0"
                    step="0.01"
                    value={mrp}
                    onChange={(e) =>
                      setMrp(
                        e.target.value
                      )
                    }
                    className="w-full rounded-lg border border-gray-300 px-4 py-2.5"
                  />

                </div>


                <div>

                  <label className="mb-2 block text-sm font-medium">
                    Selling Price *
                  </label>

                  <input
                    type="number"
                    required
                    min="0"
                    step="0.01"
                    value={
                      sellingPrice
                    }
                    onChange={(e) =>
                      setSellingPrice(
                        e.target.value
                      )
                    }
                    className="w-full rounded-lg border border-gray-300 px-4 py-2.5"
                  />

                </div>

              </div>


              {/* Inventory */}

              <div className="grid grid-cols-2 gap-4">

                <div>

                  <label className="mb-2 block text-sm font-medium">
                    Stock *
                  </label>

                  <input
                    type="number"
                    required
                    min="0"
                    value={
                      quantity
                    }
                    onChange={(e) =>
                      setQuantity(
                        e.target.value
                      )
                    }
                    className="w-full rounded-lg border border-gray-300 px-4 py-2.5"
                  />

                </div>


                <div>

                  <label className="mb-2 block text-sm font-medium">
                    Low Stock Alert
                  </label>

                  <input
                    type="number"
                    min="0"
                    value={
                      lowStockLimit
                    }
                    onChange={(e) =>
                      setLowStockLimit(
                        e.target.value
                      )
                    }
                    className="w-full rounded-lg border border-gray-300 px-4 py-2.5"
                  />

                </div>

              </div>


              {/* Status */}

              <div>

                <label className="mb-2 block text-sm font-medium">
                  Status
                </label>

                <select
                  value={status}
                  onChange={(e) =>
                    setStatus(
                      e.target.value
                    )
                  }
                  className="w-full rounded-lg border border-gray-300 px-4 py-2.5"
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

              <div className="flex justify-end gap-3 border-t border-gray-200 pt-5">

                <button
                  type="button"
                  onClick={closeForm}
                  className="rounded-lg border border-gray-300 px-4 py-2.5 text-sm"
                >
                  Cancel
                </button>


                <button
                  type="submit"
                  disabled={saving}
                  className="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white disabled:opacity-50"
                >
                  {saving
                    ? "Saving..."
                    : editing
                    ? "Update Variant"
                    : "Add Variant"}
                </button>

              </div>

            </form>

          </div>

        </div>

      )}

    </section>
  );
}