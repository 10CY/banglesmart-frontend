"use client";

import {
  FormEvent,
  useEffect,
  useState,
} from "react";

import {
  CheckCircle2,
  Truck,
} from "lucide-react";

import {
  apiFetch,
} from "@/lib/api";

type ShippingSetting = {
  id: number;

  flat_shipping_amount: string;

  free_shipping_minimum:
    | string
    | null;

  shipping_enabled: boolean;
};

export default function AdminShippingPage() {
  const [
    setting,
    setSetting,
  ] = useState<ShippingSetting | null>(
    null
  );

  const [
    flatAmount,
    setFlatAmount,
  ] = useState("");

  const [
    freeMinimum,
    setFreeMinimum,
  ] = useState("");

  const [
    enabled,
    setEnabled,
  ] = useState(true);

  const [
    loading,
    setLoading,
  ] = useState(true);

  const [
    saving,
    setSaving,
  ] = useState(false);

  const [
    error,
    setError,
  ] = useState("");

  const [
    success,
    setSuccess,
  ] = useState("");

  useEffect(() => {
    async function load() {
      try {
        const response =
          await apiFetch(
            "/admin/shipping-settings"
          );

        const data =
          await response.json();

        if (!response.ok) {
          setError(
            data.message ||
              "Unable to load shipping settings."
          );

          return;
        }

        setSetting(
          data.data
        );

        setFlatAmount(
          String(
            data.data
              .flat_shipping_amount ??
              ""
          )
        );

        setFreeMinimum(
          data.data
            .free_shipping_minimum
            ?
            String(
              data.data
                .free_shipping_minimum
            )
            :
            ""
        );

        setEnabled(
          Boolean(
            data.data
              .shipping_enabled
          )
        );
      } catch {
        setError(
          "Unable to connect to server."
        );
      } finally {
        setLoading(false);
      }
    }

    load();
  }, []);

  async function save(
    event: FormEvent
  ) {
    event.preventDefault();

    try {
      setSaving(true);
      setError("");
      setSuccess("");

      const response =
        await apiFetch(
          "/admin/shipping-settings",
          {
            method: "PUT",

            body:
              JSON.stringify({
                flat_shipping_amount:
                  Number(
                    flatAmount ||
                      0
                  ),

                free_shipping_minimum:
                  freeMinimum
                    ?
                    Number(
                      freeMinimum
                    )
                    :
                    null,

                shipping_enabled:
                  enabled,
              }),
          }
        );

      const data =
        await response.json();

      if (!response.ok) {
        setError(
          data.message ||
            Object.values(
              data.errors ||
                {}
            )
              .flat()
              .join(" ") ||
            "Unable to save shipping settings."
        );

        return;
      }

      setSetting(
        data.data
      );

      setSuccess(
        "Shipping settings saved successfully."
      );
    } catch {
      setError(
        "Unable to connect to server."
      );
    } finally {
      setSaving(false);
    }
  }

  if (loading) {
    return (
      <div className="py-20 text-center text-sm text-gray-500">
        Loading shipping settings...
      </div>
    );
  }

  return (
    <div className="max-w-3xl">

      <div className="mb-6">

        <h1 className="text-2xl font-semibold text-gray-900">
          Shipping Settings
        </h1>

        <p className="mt-1 text-sm text-gray-500">
          Configure delivery charges and free shipping rules.
        </p>

      </div>

      {error && (
        <div className="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          {error}
        </div>
      )}

      {success && (
        <div className="mb-5 flex items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">

          <CheckCircle2
            size={18}
          />

          {success}

        </div>
      )}

      <form
        onSubmit={save}
        className="rounded-xl border border-gray-200 bg-white p-6"
      >

        <div className="flex items-start gap-4 border-b border-gray-200 pb-6">

          <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gray-100">

            <Truck
              size={21}
              className="text-gray-700"
            />

          </div>

          <div>

            <h2 className="font-semibold text-gray-900">
              Delivery Charges
            </h2>

            <p className="mt-1 text-sm leading-6 text-gray-500">
              Customers below the free shipping threshold will pay the flat shipping charge.
            </p>

          </div>

        </div>

        <div className="mt-6 space-y-6">

          <label className="flex items-center justify-between gap-5 rounded-xl border border-gray-200 p-4">

            <div>

              <p className="font-medium text-gray-900">
                Enable Shipping Charges
              </p>

              <p className="mt-1 text-sm text-gray-500">
                Turn off to make all orders free shipping.
              </p>

            </div>

            <input
              type="checkbox"
              checked={enabled}
              onChange={(event) =>
                setEnabled(
                  event.target.checked
                )
              }
              className="h-5 w-5"
            />

          </label>

          <div>

            <label className="mb-2 block text-sm font-medium text-gray-700">
              Flat Shipping Charge (₹)
            </label>

            <input
              type="number"
              min="0"
              step="0.01"
              disabled={!enabled}
              value={flatAmount}
              onChange={(event) =>
                setFlatAmount(
                  event.target.value
                )
              }
              placeholder="99"
              className="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm outline-none focus:border-gray-600 disabled:bg-gray-100"
            />

          </div>

          <div>

            <label className="mb-2 block text-sm font-medium text-gray-700">
              Free Shipping Minimum (₹)
            </label>

            <input
              type="number"
              min="0"
              step="0.01"
              disabled={!enabled}
              value={freeMinimum}
              onChange={(event) =>
                setFreeMinimum(
                  event.target.value
                )
              }
              placeholder="2000"
              className="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm outline-none focus:border-gray-600 disabled:bg-gray-100"
            />

            <p className="mt-2 text-xs leading-5 text-gray-400">
              Leave blank if you never want automatic free shipping.
            </p>

          </div>

        </div>

        {/* Preview */}

        <div className="mt-6 rounded-xl bg-gray-50 p-4">

          <p className="text-xs font-medium uppercase tracking-wide text-gray-400">
            Current Rule
          </p>

          <p className="mt-2 text-sm text-gray-700">

            {!enabled
              ? "Free shipping on every order."
              : (
                <>
                  Charge{" "}
                  <strong>
                    ₹
                    {Number(
                      flatAmount ||
                        0
                    ).toLocaleString(
                      "en-IN"
                    )}
                  </strong>

                  {freeMinimum
                    ? (
                      <>
                        {" "}
                        and give free shipping on orders of{" "}
                        <strong>
                          ₹
                          {Number(
                            freeMinimum
                          ).toLocaleString(
                            "en-IN"
                          )}
                        </strong>{" "}
                        or more.
                      </>
                    )
                    : "."}
                </>
              )}

          </p>

        </div>

        <div className="mt-6 flex justify-end">

          <button
            type="submit"
            disabled={saving}
            className="rounded-lg bg-gray-900 px-6 py-2.5 text-sm font-medium text-white transition hover:bg-black disabled:opacity-50"
          >
            {saving
              ? "Saving..."
              : "Save Shipping Settings"}
          </button>

        </div>

      </form>

    </div>
  );
}