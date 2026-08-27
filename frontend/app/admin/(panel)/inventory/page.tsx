"use client";

import {
  FormEvent,
  useCallback,
  useEffect,
  useState,
} from "react";

import Link from "next/link";

import {
  AlertTriangle,
  Boxes,
  History,
  PackageCheck,
  PackageX,
  Pencil,
  Search,
  X,
} from "lucide-react";

import {
  apiFetch,
  BACKEND_URL,
} from "@/lib/api";

/* -------------------------------------------------------------------------- */
/* Types                                                                      */
/* -------------------------------------------------------------------------- */

type ProductImage = {
  id: number;
  image: string;
  is_primary: boolean;
};

type Product = {
  id: number;
  name: string;
  sku: string | null;
  primary_image: ProductImage | null;
};

type Size = {
  id: number;
  name: string;
  display_name: string | null;
};

type Color = {
  id: number;
  name: string;
  display_name: string | null;
  hex_code: string | null;
};

type Variant = {
  id: number;
  sku: string;
  status: string;

  product: Product;
  size: Size;
  color: Color;
};

type Inventory = {
  id: number;

  product_variant_id: number;

  quantity: number;
  reserved_quantity: number;
  available_quantity: number;

  low_stock_limit: number;

  variant: Variant;
};

type Summary = {
  total_variants: number;
  available_units: number;
  low_stock: number;
  out_of_stock: number;
};

type Pagination = {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
};

/* -------------------------------------------------------------------------- */
/* Page                                                                       */
/* -------------------------------------------------------------------------- */

export default function InventoryPage() {
  /* ------------------------------------------------------------------------ */
  /* Inventory State                                                          */
  /* ------------------------------------------------------------------------ */

  const [
    inventories,
    setInventories,
  ] = useState<Inventory[]>([]);

  const [
    summary,
    setSummary,
  ] = useState<Summary>({
    total_variants: 0,
    available_units: 0,
    low_stock: 0,
    out_of_stock: 0,
  });

  const [
    pagination,
    setPagination,
  ] = useState<Pagination>({
    current_page: 1,
    last_page: 1,
    per_page: 25,
    total: 0,
  });

  const [
    loading,
    setLoading,
  ] = useState(true);

  /* ------------------------------------------------------------------------ */
  /* Filters                                                                  */
  /* ------------------------------------------------------------------------ */

  const [
    search,
    setSearch,
  ] = useState("");

  const [
    stockStatus,
    setStockStatus,
  ] = useState("");

  const [
    variantStatus,
    setVariantStatus,
  ] = useState("");

  const [
    page,
    setPage,
  ] = useState(1);

  /* ------------------------------------------------------------------------ */
  /* Edit Inventory Drawer                                                    */
  /* ------------------------------------------------------------------------ */

  const [
    editing,
    setEditing,
  ] = useState<Inventory | null>(
    null
  );

  const [
    showEdit,
    setShowEdit,
  ] = useState(false);

  const [
    quantity,
    setQuantity,
  ] = useState("");

  const [
    lowStockLimit,
    setLowStockLimit,
  ] = useState("");

  const [
    notes,
    setNotes,
  ] = useState("");

  const [
    saving,
    setSaving,
  ] = useState(false);

  const [
    error,
    setError,
  ] = useState("");

  /* ------------------------------------------------------------------------ */
  /* Load Inventory                                                           */
  /* ------------------------------------------------------------------------ */

  const loadInventory =
    useCallback(async () => {
      try {
        setLoading(true);

        const params =
          new URLSearchParams();

        if (search.trim()) {
          params.set(
            "search",
            search.trim()
          );
        }

        if (stockStatus) {
          params.set(
            "stock_status",
            stockStatus
          );
        }

        if (variantStatus) {
          params.set(
            "status",
            variantStatus
          );
        }

        params.set(
          "page",
          String(page)
        );

        const response =
          await apiFetch(
            `/admin/inventory?${params.toString()}`
          );

        const data =
          await response.json();

        if (!response.ok) {
          console.error(
            data.message ||
              "Unable to load inventory."
          );

          return;
        }

        const paginator =
          data.data;

        setInventories(
          paginator.data || []
        );

        setPagination({
          current_page:
            paginator.current_page || 1,

          last_page:
            paginator.last_page || 1,

          per_page:
            paginator.per_page || 25,

          total:
            paginator.total || 0,
        });

        setSummary(
          data.summary || {
            total_variants: 0,
            available_units: 0,
            low_stock: 0,
            out_of_stock: 0,
          }
        );
      } catch (error) {
        console.error(
          "Inventory error:",
          error
        );
      } finally {
        setLoading(false);
      }
    }, [
      search,
      stockStatus,
      variantStatus,
      page,
    ]);

  /* ------------------------------------------------------------------------ */
  /* Debounced Load                                                           */
  /* ------------------------------------------------------------------------ */

  useEffect(() => {
    const timer =
      window.setTimeout(() => {
        loadInventory();
      }, 300);

    return () =>
      window.clearTimeout(timer);
  }, [loadInventory]);

  /* ------------------------------------------------------------------------ */
  /* Reset Pagination when filter changes                                     */
  /* ------------------------------------------------------------------------ */

  useEffect(() => {
    setPage(1);
  }, [
    search,
    stockStatus,
    variantStatus,
  ]);

  /* ------------------------------------------------------------------------ */
  /* Open Edit Drawer                                                         */
  /* ------------------------------------------------------------------------ */

  function openEdit(
    inventory: Inventory
  ) {
    setEditing(
      inventory
    );

    setQuantity(
      String(
        inventory.quantity
      )
    );

    setLowStockLimit(
      String(
        inventory.low_stock_limit
      )
    );

    setNotes("");

    setError("");

    setShowEdit(true);
  }

  /* ------------------------------------------------------------------------ */
  /* Close Edit Drawer                                                        */
  /* ------------------------------------------------------------------------ */

  function closeEdit() {
    setShowEdit(false);

    setEditing(null);

    setQuantity("");

    setLowStockLimit("");

    setNotes("");

    setError("");
  }

  /* ------------------------------------------------------------------------ */
  /* Update Inventory                                                         */
  /* ------------------------------------------------------------------------ */

  async function handleUpdate(
    event: FormEvent<HTMLFormElement>
  ) {
    event.preventDefault();

    if (!editing) {
      return;
    }

    setSaving(true);
    setError("");

    try {
      const response =
        await apiFetch(
          `/admin/inventory/${editing.id}`,
          {
            method: "PUT",

            body: JSON.stringify({
              quantity:
                Number(quantity),

              low_stock_limit:
                Number(
                  lowStockLimit
                ),

              notes:
                notes.trim()
                  ? notes.trim()
                  : null,
            }),
          }
        );

      const data =
        await response.json();

      if (!response.ok) {
        const validationErrors =
          data.errors as
            | Record<
                string,
                string[]
              >
            | undefined;

        const firstError =
          validationErrors
            ? Object.values(
                validationErrors
              )[0]?.[0]
            : undefined;

        setError(
          firstError ||
            data.message ||
            "Unable to update inventory."
        );

        return;
      }

      closeEdit();

      await loadInventory();
    } catch {
      setError(
        "Unable to connect to server."
      );
    } finally {
      setSaving(false);
    }
  }

  /* ------------------------------------------------------------------------ */
  /* Stock Status                                                             */
  /* ------------------------------------------------------------------------ */

  function getStockStatus(
    inventory: Inventory
  ) {
    const available =
      inventory.available_quantity;

    if (available <= 0) {
      return {
        label: "Out of stock",
        className:
          "bg-red-50 text-red-700",
      };
    }

    if (
      available <=
      inventory.low_stock_limit
    ) {
      return {
        label: "Low stock",
        className:
          "bg-orange-50 text-orange-700",
      };
    }

    return {
      label: "In stock",
      className:
        "bg-green-50 text-green-700",
    };
  }

  /* ------------------------------------------------------------------------ */
  /* Render                                                                   */
  /* ------------------------------------------------------------------------ */

  return (
    <div>

      {/* Header */}

      <div className="mb-6 flex flex-wrap items-center justify-between gap-4">

        <div>
          <h1 className="text-2xl font-semibold text-gray-900">
            Inventory
          </h1>

          <p className="mt-1 text-sm text-gray-500">
            Manage stock across all
            product variants.
          </p>
        </div>

        <Link
          href="/admin/inventory/history"
          className="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
        >
          <History
            size={18}
          />

          Stock History
        </Link>

      </div>

      {/* Summary Cards */}

      <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {/* Total Variants */}

        <div className="rounded-xl border border-gray-200 bg-white p-5">

          <div className="flex items-center justify-between">

            <div>
              <p className="text-sm text-gray-500">
                Total Variants
              </p>

              <p className="mt-2 text-2xl font-semibold text-gray-900">
                {
                  summary.total_variants
                }
              </p>
            </div>

            <div className="rounded-lg bg-gray-100 p-3 text-gray-700">
              <Boxes
                size={22}
              />
            </div>

          </div>

        </div>

        {/* Available Units */}

        <div className="rounded-xl border border-gray-200 bg-white p-5">

          <div className="flex items-center justify-between">

            <div>
              <p className="text-sm text-gray-500">
                Available Units
              </p>

              <p className="mt-2 text-2xl font-semibold text-gray-900">
                {
                  summary.available_units
                }
              </p>
            </div>

            <div className="rounded-lg bg-green-50 p-3 text-green-700">
              <PackageCheck
                size={22}
              />
            </div>

          </div>

        </div>

        {/* Low Stock */}

        <div className="rounded-xl border border-gray-200 bg-white p-5">

          <div className="flex items-center justify-between">

            <div>
              <p className="text-sm text-gray-500">
                Low Stock
              </p>

              <p className="mt-2 text-2xl font-semibold text-gray-900">
                {
                  summary.low_stock
                }
              </p>
            </div>

            <div className="rounded-lg bg-orange-50 p-3 text-orange-700">
              <AlertTriangle
                size={22}
              />
            </div>

          </div>

        </div>

        {/* Out of Stock */}

        <div className="rounded-xl border border-gray-200 bg-white p-5">

          <div className="flex items-center justify-between">

            <div>
              <p className="text-sm text-gray-500">
                Out of Stock
              </p>

              <p className="mt-2 text-2xl font-semibold text-gray-900">
                {
                  summary.out_of_stock
                }
              </p>
            </div>

            <div className="rounded-lg bg-red-50 p-3 text-red-700">
              <PackageX
                size={22}
              />
            </div>

          </div>

        </div>

      </div>

      {/* Filters */}

      <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4">

        <div className="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_200px_180px]">

          {/* Search */}

          <div className="relative">

            <Search
              size={18}
              className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
            />

            <input
              type="text"
              value={search}
              onChange={(event) =>
                setSearch(
                  event.target.value
                )
              }
              placeholder="Search product or SKU..."
              className="w-full rounded-lg border border-gray-300 py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-gray-500"
            />

          </div>

          {/* Stock Filter */}

          <select
            value={stockStatus}
            onChange={(event) =>
              setStockStatus(
                event.target.value
              )
            }
            className="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none"
          >
            <option value="">
              All stock
            </option>

            <option value="in_stock">
              In stock
            </option>

            <option value="low">
              Low stock
            </option>

            <option value="out">
              Out of stock
            </option>
          </select>

          {/* Variant Status */}

          <select
            value={
              variantStatus
            }
            onChange={(event) =>
              setVariantStatus(
                event.target.value
              )
            }
            className="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none"
          >
            <option value="">
              All status
            </option>

            <option value="active">
              Active
            </option>

            <option value="inactive">
              Inactive
            </option>
          </select>

        </div>

      </div>

      {/* Inventory Table */}

      <div className="overflow-hidden rounded-xl border border-gray-200 bg-white">

        <div className="overflow-x-auto">

          <table className="w-full min-w-[1050px]">

            <thead className="border-b border-gray-200 bg-gray-50">

              <tr>

                <th className="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                  Product
                </th>

                <th className="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                  Variant
                </th>

                <th className="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                  SKU
                </th>

                <th className="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                  Total
                </th>

                <th className="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                  Reserved
                </th>

                <th className="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                  Available
                </th>

                <th className="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                  Stock Status
                </th>

                <th className="px-5 py-3 text-right text-xs font-medium uppercase tracking-wide text-gray-500">
                  Action
                </th>

              </tr>

            </thead>

            <tbody className="divide-y divide-gray-200">

              {loading ? (

                <tr>
                  <td
                    colSpan={8}
                    className="px-5 py-14 text-center text-sm text-gray-500"
                  >
                    Loading inventory...
                  </td>
                </tr>

              ) : inventories.length === 0 ? (

                <tr>
                  <td
                    colSpan={8}
                    className="px-5 py-14 text-center"
                  >
                    <Boxes
                      size={36}
                      className="mx-auto text-gray-300"
                    />

                    <p className="mt-3 text-sm font-medium text-gray-700">
                      No inventory found
                    </p>

                    <p className="mt-1 text-sm text-gray-500">
                      Add product variants
                      to create inventory.
                    </p>
                  </td>
                </tr>

              ) : (

                inventories.map(
                  (inventory) => {

                    const variant =
                      inventory.variant;

                    const product =
                      variant?.product;

                    const size =
                      variant?.size;

                    const color =
                      variant?.color;

                    const stock =
                      getStockStatus(
                        inventory
                      );

                    const image =
                      product
                        ?.primary_image
                        ?.image;

                    return (
                      <tr
                        key={
                          inventory.id
                        }
                        className="transition hover:bg-gray-50"
                      >

                        {/* Product */}

                        <td className="px-5 py-4">

                          <div className="flex items-center gap-3">

                            <div className="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-gray-50">

                              {image ? (

                                <img
                                  src={`${BACKEND_URL}/storage/${image}`}
                                  alt={
                                    product?.name ||
                                    "Product"
                                  }
                                  className="h-full w-full object-cover"
                                />

                              ) : (

                                <Boxes
                                  size={20}
                                  className="text-gray-300"
                                />

                              )}

                            </div>

                            <div>

                              <p className="font-medium text-gray-900">
                                {
                                  product?.name ||
                                  "—"
                                }
                              </p>

                              {product?.sku && (
                                <p className="mt-1 text-xs text-gray-400">
                                  Base SKU:{" "}
                                  {
                                    product.sku
                                  }
                                </p>
                              )}

                            </div>

                          </div>

                        </td>

                        {/* Variant */}

                        <td className="px-5 py-4">

                          <div className="flex items-center gap-2">

                            <span
                              className="h-5 w-5 shrink-0 rounded-full border border-gray-300"
                              style={{
                                backgroundColor:
                                  color
                                    ?.hex_code ||
                                  "#ffffff",
                              }}
                            />

                            <span className="text-sm text-gray-700">

                              {size?.display_name ||
                                size?.name ||
                                "—"}

                              {" / "}

                              {color?.display_name ||
                                color?.name ||
                                "—"}

                            </span>

                          </div>

                        </td>

                        {/* SKU */}

                        <td className="px-5 py-4">

                          <span className="font-mono text-sm text-gray-600">
                            {
                              variant?.sku ||
                              "—"
                            }
                          </span>

                        </td>

                        {/* Total */}

                        <td className="px-5 py-4">

                          <span className="text-sm font-medium text-gray-900">
                            {
                              inventory.quantity
                            }
                          </span>

                        </td>

                        {/* Reserved */}

                        <td className="px-5 py-4">

                          <span className="text-sm text-gray-600">
                            {
                              inventory.reserved_quantity
                            }
                          </span>

                        </td>

                        {/* Available */}

                        <td className="px-5 py-4">

                          <span className="text-sm font-semibold text-gray-900">
                            {
                              inventory.available_quantity
                            }
                          </span>

                        </td>

                        {/* Stock Status */}

                        <td className="px-5 py-4">

                          <span
                            className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${stock.className}`}
                          >
                            {stock.label}
                          </span>

                        </td>

                        {/* Action */}

                        <td className="px-5 py-4">

                          <div className="flex justify-end">

                            <button
                              type="button"
                              onClick={() =>
                                openEdit(
                                  inventory
                                )
                              }
                              title="Update inventory"
                              className="rounded-lg border border-gray-200 p-2 text-gray-700 transition hover:bg-gray-100"
                            >
                              <Pencil
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

        {/* Pagination */}

        {!loading &&
          pagination.total > 0 && (

          <div className="flex flex-wrap items-center justify-between gap-4 border-t border-gray-200 px-5 py-4">

            <p className="text-sm text-gray-500">

              Page{" "}
              {
                pagination.current_page
              }{" "}
              of{" "}
              {
                pagination.last_page
              }

              {" · "}

              {
                pagination.total
              }{" "}
              variants

            </p>

            <div className="flex gap-2">

              <button
                type="button"
                disabled={
                  pagination.current_page <= 1
                }
                onClick={() =>
                  setPage(
                    (current) =>
                      Math.max(
                        1,
                        current - 1
                      )
                  )
                }
                className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
              >
                Previous
              </button>

              <button
                type="button"
                disabled={
                  pagination.current_page >=
                  pagination.last_page
                }
                onClick={() =>
                  setPage(
                    (current) =>
                      current + 1
                  )
                }
                className="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40"
              >
                Next
              </button>

            </div>

          </div>

        )}

      </div>

      {/* Edit Inventory Drawer */}

      {showEdit &&
        editing && (

        <div className="fixed inset-0 z-50 flex justify-end bg-black/30">

          <div className="h-full w-full max-w-md overflow-y-auto bg-white shadow-2xl">

            {/* Header */}

            <div className="sticky top-0 z-10 flex items-center justify-between border-b border-gray-200 bg-white px-6 py-5">

              <div>

                <h2 className="text-lg font-semibold text-gray-900">
                  Update Inventory
                </h2>

                <p className="mt-1 text-sm text-gray-500">
                  {
                    editing.variant
                      ?.product
                      ?.name
                  }
                </p>

              </div>

              <button
                type="button"
                onClick={
                  closeEdit
                }
                className="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900"
              >
                <X
                  size={20}
                />
              </button>

            </div>

            {/* Form */}

            <form
              onSubmit={
                handleUpdate
              }
              className="space-y-6 p-6"
            >

              {error && (
                <div className="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                  {error}
                </div>
              )}

              {/* Variant */}

              <div className="rounded-xl border border-gray-200 bg-gray-50 p-4">

                <p className="text-xs font-medium uppercase tracking-wide text-gray-500">
                  Variant
                </p>

                <div className="mt-3 flex items-center gap-2">

                  <span
                    className="h-6 w-6 shrink-0 rounded-full border border-gray-300"
                    style={{
                      backgroundColor:
                        editing.variant
                          ?.color
                          ?.hex_code ||
                        "#ffffff",
                    }}
                  />

                  <p className="text-sm font-medium text-gray-900">

                    {editing.variant
                      ?.size
                      ?.display_name ||
                      editing.variant
                        ?.size
                        ?.name ||
                      "—"}

                    {" / "}

                    {editing.variant
                      ?.color
                      ?.display_name ||
                      editing.variant
                        ?.color
                        ?.name ||
                      "—"}

                  </p>

                </div>

                <p className="mt-3 font-mono text-xs text-gray-500">
                  SKU:{" "}
                  {
                    editing.variant
                      ?.sku
                  }
                </p>

              </div>

              {/* Quantity */}

              <div>

                <label className="mb-2 block text-sm font-medium text-gray-700">
                  Total Quantity *
                </label>

                <input
                  type="number"
                  required
                  min={
                    editing.reserved_quantity
                  }
                  value={
                    quantity
                  }
                  onChange={(event) =>
                    setQuantity(
                      event.target.value
                    )
                  }
                  className="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-600"
                />

                <p className="mt-2 text-xs text-gray-500">
                  Minimum allowed:{" "}
                  {
                    editing.reserved_quantity
                  }{" "}
                  because this stock is
                  currently reserved.
                </p>

              </div>

              {/* Low Stock */}

              <div>

                <label className="mb-2 block text-sm font-medium text-gray-700">
                  Low Stock Alert *
                </label>

                <input
                  type="number"
                  required
                  min="0"
                  value={
                    lowStockLimit
                  }
                  onChange={(event) =>
                    setLowStockLimit(
                      event.target.value
                    )
                  }
                  className="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-600"
                />

                <p className="mt-2 text-xs text-gray-500">
                  Mark this variant as
                  low stock when available
                  quantity reaches this
                  number.
                </p>

              </div>

              {/* Adjustment Notes */}

              <div>

                <label className="mb-2 block text-sm font-medium text-gray-700">
                  Adjustment Notes
                </label>

                <textarea
                  rows={4}
                  value={notes}
                  onChange={(event) =>
                    setNotes(
                      event.target.value
                    )
                  }
                  placeholder="Example: Received 10 units from supplier"
                  className="w-full resize-none rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-600"
                />

                <p className="mt-2 text-xs text-gray-500">
                  This note will appear
                  in stock history when
                  quantity changes.
                </p>

              </div>

              {/* Stock Calculation */}

              <div className="rounded-xl border border-gray-200 bg-gray-50 p-4">

                <h3 className="mb-4 text-sm font-medium text-gray-900">
                  Stock Summary
                </h3>

                <div className="flex items-center justify-between">

                  <span className="text-sm text-gray-600">
                    Current Total
                  </span>

                  <span className="font-medium text-gray-900">
                    {
                      editing.quantity
                    }
                  </span>

                </div>

                <div className="mt-3 flex items-center justify-between">

                  <span className="text-sm text-gray-600">
                    Reserved
                  </span>

                  <span className="font-medium text-gray-900">
                    {
                      editing.reserved_quantity
                    }
                  </span>

                </div>

                <div className="mt-3 flex items-center justify-between border-t border-gray-200 pt-3">

                  <span className="text-sm font-medium text-gray-700">
                    New Total
                  </span>

                  <span className="font-semibold text-gray-900">
                    {
                      Number(
                        quantity || 0
                      )
                    }
                  </span>

                </div>

                <div className="mt-3 flex items-center justify-between">

                  <span className="text-sm font-medium text-gray-700">
                    New Available
                  </span>

                  <span className="font-semibold text-gray-900">
                    {Math.max(
                      0,

                      Number(
                        quantity || 0
                      ) -
                        editing.reserved_quantity
                    )}
                  </span>

                </div>

                {/* Difference */}

                {Number(quantity || 0) !==
                  editing.quantity && (

                  <div className="mt-4 rounded-lg border border-gray-200 bg-white p-3">

                    <div className="flex items-center justify-between">

                      <span className="text-sm text-gray-600">
                        Stock Change
                      </span>

                      <span
                        className={`font-semibold ${
                          Number(
                            quantity ||
                              0
                          ) -
                            editing.quantity >
                          0
                            ? "text-green-700"
                            : "text-red-600"
                        }`}
                      >

                        {Number(
                          quantity ||
                            0
                        ) -
                          editing.quantity >
                        0
                          ? "+"
                          : ""}

                        {Number(
                          quantity ||
                            0
                        ) -
                          editing.quantity}

                      </span>

                    </div>

                  </div>

                )}

              </div>

              {/* Actions */}

              <div className="flex justify-end gap-3 border-t border-gray-200 pt-6">

                <button
                  type="button"
                  onClick={
                    closeEdit
                  }
                  className="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                >
                  Cancel
                </button>

                <button
                  type="submit"
                  disabled={
                    saving
                  }
                  className="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-black disabled:cursor-not-allowed disabled:opacity-50"
                >
                  {saving
                    ? "Updating..."
                    : "Update Stock"}
                </button>

              </div>

            </form>

          </div>

        </div>

      )}

    </div>
  );
}