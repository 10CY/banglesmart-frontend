"use client";

import {
  FormEvent,
  useEffect,
  useState,
} from "react";

import Link from "next/link";

import {
  ArrowLeft,
  CalendarDays,
  CheckCircle2,
  Edit3,
  Mail,
  Phone,
  Save,
  UserRound,
  X,
  XCircle,
} from "lucide-react";

import {
  useParams,
} from "next/navigation";

import {
  apiFetch,
} from "@/lib/api";

/* -------------------------------------------------------------------------- */
/* Types                                                                      */
/* -------------------------------------------------------------------------- */

type Customer = {

  id:number;

  name:string;

  email:string;

  phone:string | null;

  status:string;

  created_at:string;

};

/* -------------------------------------------------------------------------- */
/* Page                                                                       */
/* -------------------------------------------------------------------------- */

export default function CustomerDetailsPage() {
  const params =
    useParams();

  const customerId =
    String(params.id);

  const [
    customer,
    setCustomer,
  ] = useState<Customer | null>(
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

  /* ------------------------------------------------------------------------ */
  /* Edit                                                                      */
  /* ------------------------------------------------------------------------ */

  const [
    editing,
    setEditing,
  ] = useState(false);

  const [
    name,
    setName,
  ] = useState("");

  const [
    email,
    setEmail,
  ] = useState("");

  const [
    status,
    setStatus,
  ] = useState("active");

  const [
    saving,
    setSaving,
  ] = useState(false);

  const [
    formError,
    setFormError,
  ] = useState("");

  /* ------------------------------------------------------------------------ */
  /* Load Customer                                                            */
  /* ------------------------------------------------------------------------ */

  async function loadCustomer() {
    try {
      setLoading(true);
      setError("");

      const response =
        await apiFetch(
          `/admin/customers/${customerId}`
        );

      const data =
        await response.json();

      if (!response.ok) {
        setError(
          data.message ||
            "Unable to load customer."
        );

        return;
      }

      const item =
        data.data;

      setCustomer(item);

      setName(
        item.name || ""
      );

      setEmail(
        item.email || ""
      );

      setStatus(
        item.status || "active"
      );
    } catch {
      setError(
        "Unable to connect to server."
      );
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    loadCustomer();
  }, [customerId]);

  /* ------------------------------------------------------------------------ */
  /* Edit Customer                                                            */
  /* ------------------------------------------------------------------------ */

  async function handleSubmit(
    event: FormEvent<HTMLFormElement>
  ) {
    event.preventDefault();

    setSaving(true);
    setFormError("");

    try {
      const response =
        await apiFetch(
          `/admin/customers/${customerId}`,
          {
            method: "PUT",

            body: JSON.stringify({
              name,
              email,
              status,
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

        setFormError(
          firstError ||
            data.message ||
            "Unable to update customer."
        );

        return;
      }

      setCustomer(
        data.data
      );

      setEditing(false);
    } catch {
      setFormError(
        "Unable to connect to server."
      );
    } finally {
      setSaving(false);
    }
  }

  /* ------------------------------------------------------------------------ */
  /* Quick Status Update                                                      */
  /* ------------------------------------------------------------------------ */

  async function updateStatus(
    newStatus: "active" | "inactive"
  ) {
    if (!customer) {
      return;
    }

    const message =
      newStatus === "inactive"
        ? "Deactivate this customer account?"
        : "Activate this customer account?";

    if (
      !window.confirm(message)
    ) {
      return;
    }

    try {
      const response =
        await apiFetch(
          `/admin/customers/${customerId}/status`,
          {
            method: "PUT",

            body: JSON.stringify({
              status: newStatus,
            }),
          }
        );

      const data =
        await response.json();

      if (!response.ok) {
        window.alert(
          data.message ||
            "Unable to update customer status."
        );

        return;
      }

      setCustomer(
        data.data
      );

      setStatus(
        data.data.status
      );
    } catch {
      window.alert(
        "Unable to connect to server."
      );
    }
  }

  /* ------------------------------------------------------------------------ */
  /* Cancel Editing                                                           */
  /* ------------------------------------------------------------------------ */

  function cancelEdit() {
    if (!customer) {
      return;
    }

    setName(
      customer.name
    );

    setEmail(
      customer.email
    );

    setStatus(
      customer.status
    );

    setFormError("");

    setEditing(false);
  }

  /* ------------------------------------------------------------------------ */
  /* Date                                                                     */
  /* ------------------------------------------------------------------------ */

  function formatDate(
    value: string
  ) {
    return new Intl.DateTimeFormat(
      "en-IN",
      {
        dateStyle: "long",
        timeStyle: "short",
      }
    ).format(
      new Date(value)
    );
  }

  /* ------------------------------------------------------------------------ */
  /* Loading                                                                  */
  /* ------------------------------------------------------------------------ */

  if (loading) {
    return (
      <div className="flex min-h-[450px] items-center justify-center">

        <p className="text-sm text-gray-500">
          Loading customer...
        </p>

      </div>
    );
  }

  /* ------------------------------------------------------------------------ */
  /* Error                                                                    */
  /* ------------------------------------------------------------------------ */

  if (
    error ||
    !customer
  ) {
    return (
      <div>

        <Link
          href="/admin/customers"
          className="mb-5 inline-flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900"
        >
          <ArrowLeft
            size={17}
          />

          Back to customers
        </Link>

        <div className="rounded-xl border border-red-200 bg-red-50 p-6 text-sm text-red-700">
          {error ||
            "Customer not found."}
        </div>

      </div>
    );
  }

  /* ------------------------------------------------------------------------ */
  /* UI                                                                       */
  /* ------------------------------------------------------------------------ */

  return (
    <div>

      {/* Header */}

      <div className="mb-6 flex flex-wrap items-center justify-between gap-4">

        <div className="flex items-center gap-4">

          <Link
            href="/admin/customers"
            className="rounded-lg border border-gray-200 bg-white p-2 text-gray-700 transition hover:bg-gray-100"
          >
            <ArrowLeft
              size={19}
            />
          </Link>

          <div>

            <h1 className="text-2xl font-semibold text-gray-900">
              Customer Details
            </h1>

            <p className="mt-1 text-sm text-gray-500">
              View and manage customer
              account information.
            </p>

          </div>

        </div>

        <Link
  href={`/admin/customers/${customer.id}/edit`}
  className="flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-black"
>
  <Edit3
    size={17}
  />

  Edit Customer
</Link>

      </div>

      {/* Customer Summary */}

      <div className="mb-6 rounded-xl border border-gray-200 bg-white p-6">

        <div className="flex flex-wrap items-center justify-between gap-5">

          <div className="flex items-center gap-4">

            <div className="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">

              <span className="text-xl font-semibold text-gray-700">

                {customer.name
                  ?.charAt(0)
                  .toUpperCase() ||
                  "C"}

              </span>

            </div>

            <div>

              <div className="flex flex-wrap items-center gap-3">

                <h2 className="text-xl font-semibold text-gray-900">
                  {customer.name}
                </h2>

                <span
                  className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium capitalize ${
                    customer.status ===
                    "active"
                      ? "bg-green-50 text-green-700"
                      : "bg-red-50 text-red-700"
                  }`}
                >
                  {customer.status}
                </span>

              </div>

              <p className="mt-1 text-sm text-gray-500">
                Customer #
                {customer.id}
              </p>

            </div>

          </div>

          {/* Quick Status */}

          <div>

            {customer.status ===
            "active" ? (

              <button
                type="button"
                onClick={() =>
                  updateStatus(
                    "inactive"
                  )
                }
                className="flex items-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50"
              >
                <XCircle
                  size={17}
                />

                Deactivate
              </button>

            ) : (

              <button
                type="button"
                onClick={() =>
                  updateStatus(
                    "active"
                  )
                }
                className="flex items-center gap-2 rounded-lg border border-green-200 bg-white px-4 py-2.5 text-sm font-medium text-green-700 transition hover:bg-green-50"
              >
                <CheckCircle2
                  size={17}
                />

                Activate
              </button>

            )}

          </div>

        </div>

      </div>

      {/* Layout */}

      <div className="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">

        {/* LEFT */}

        <div className="space-y-6">

          {/* Edit Form */}

          {editing ? (

            <section className="rounded-xl border border-gray-200 bg-white p-6">

              <div className="mb-5 flex items-center justify-between">

                <div>

                  <h2 className="font-semibold text-gray-900">
                    Edit Customer
                  </h2>

                  <p className="mt-1 text-sm text-gray-500">
                    Update customer
                    information.
                  </p>

                </div>

                <button
                  type="button"
                  onClick={cancelEdit}
                  className="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100"
                >
                  <X size={19} />
                </button>

              </div>

              {formError && (
                <div className="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                  {formError}
                </div>
              )}

              <form
                onSubmit={
                  handleSubmit
                }
                className="space-y-5"
              >

                {/* Name */}

                <div>

                  <label className="mb-2 block text-sm font-medium text-gray-700">
                    Customer Name *
                  </label>

                  <input
                    type="text"
                    required
                    value={name}
                    onChange={(event) =>
                      setName(
                        event.target.value
                      )
                    }
                    className="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-600"
                  />

                </div>

                {/* Email */}

                <div>

                  <label className="mb-2 block text-sm font-medium text-gray-700">
                    Email Address *
                  </label>

                  <input
                    type="email"
                    required
                    value={email}
                    onChange={(event) =>
                      setEmail(
                        event.target.value
                      )
                    }
                    className="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm text-gray-900 outline-none transition focus:border-gray-600"
                  />

                </div>

                {/* Status */}

                <div>

                  <label className="mb-2 block text-sm font-medium text-gray-700">
                    Status
                  </label>

                  <select
                    value={status}
                    onChange={(event) =>
                      setStatus(
                        event.target.value
                      )
                    }
                    className="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none"
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
                    onClick={cancelEdit}
                    className="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                  >
                    Cancel
                  </button>

                  <button
                    type="submit"
                    disabled={saving}
                    className="flex items-center gap-2 rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-black disabled:opacity-50"
                  >
                    <Save
                      size={17}
                    />

                    {saving
                      ? "Saving..."
                      : "Save Changes"}
                  </button>

                </div>

              </form>

            </section>

          ) : (

            /* Customer Info */

            <section className="rounded-xl border border-gray-200 bg-white">

              <div className="border-b border-gray-200 px-6 py-5">

                <h2 className="font-semibold text-gray-900">
                  Customer Information
                </h2>

              </div>

              <div className="divide-y divide-gray-100">

                {/* Name */}

                <div className="flex items-center gap-4 px-6 py-5">

                  <div className="rounded-lg bg-gray-100 p-2.5 text-gray-600">

                    <UserRound
                      size={19}
                    />

                  </div>

                  <div>

                    <p className="text-xs font-medium uppercase tracking-wide text-gray-400">
                      Name
                    </p>

                    <p className="mt-1 text-sm font-medium text-gray-900">
                      {customer.name}
                    </p>

                  </div>

                </div>

                {/* Email */}

                <div className="flex items-center gap-4 px-6 py-5">

                  <div className="rounded-lg bg-gray-100 p-2.5 text-gray-600">

                    <Mail
                      size={19}
                    />

                  </div>

                  <div>

                    <p className="text-xs font-medium uppercase tracking-wide text-gray-400">
                      Email
                    </p>

                    <p className="mt-1 text-sm font-medium text-gray-900">
                      {customer.email}
                    </p>

                  </div>

                </div>

                {/* PHONE */}

                <div className="flex items-center gap-4 px-6 py-5">

                  <div className="rounded-lg bg-gray-100 p-2.5 text-gray-600">

                    <Phone
                      size={19}
                    />

                  </div>


                  <div>

                    <p className="text-xs font-medium uppercase tracking-wide text-gray-400">
                      Phone
                    </p>


                    <p className="mt-1 text-sm font-medium text-gray-900">

                      {
                        customer.phone
                        ?
                        customer.phone
                        :
                        "Not added"
                      }

                    </p>


                  </div>


                </div>

                {/* Joined */}

                <div className="flex items-center gap-4 px-6 py-5">

                  <div className="rounded-lg bg-gray-100 p-2.5 text-gray-600">

                    <CalendarDays
                      size={19}
                    />

                  </div>

                  <div>

                    <p className="text-xs font-medium uppercase tracking-wide text-gray-400">
                      Joined
                    </p>

                    <p className="mt-1 text-sm font-medium text-gray-900">
                      {formatDate(
                        customer.created_at
                      )}
                    </p>

                  </div>

                </div>

              </div>

            </section>

          )}

          {/* Addresses Placeholder */}

          <section className="rounded-xl border border-gray-200 bg-white p-6">

            <div className="flex items-center justify-between">

              <div>

                <h2 className="font-semibold text-gray-900">
                  Addresses
                </h2>

                <p className="mt-1 text-sm text-gray-500">
                  Customer shipping and
                  billing addresses.
                </p>

              </div>

            </div>

            <div className="mt-5 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 p-8 text-center">

              <p className="text-sm font-medium text-gray-700">
                No addresses yet
              </p>

              <p className="mt-1 text-sm text-gray-500">
                Customer addresses will
                appear here after we add
                the Address module.
              </p>

            </div>

          </section>

          {/* Orders Placeholder */}

          <section className="rounded-xl border border-gray-200 bg-white p-6">

            <h2 className="font-semibold text-gray-900">
              Orders
            </h2>

            <div className="mt-5 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 p-8 text-center">

              <p className="text-sm font-medium text-gray-700">
                No orders yet
              </p>

              <p className="mt-1 text-sm text-gray-500">
                Customer order history
                will appear here after
                the Orders module is
                created.
              </p>

            </div>

          </section>

        </div>

        {/* RIGHT */}

        <div className="space-y-6">

          {/* Account Status */}

          <section className="rounded-xl border border-gray-200 bg-white p-5">

            <h2 className="font-semibold text-gray-900">
              Account Status
            </h2>

            <div className="mt-4">

              <span
                className={`inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium ${
                  customer.status ===
                  "active"
                    ? "bg-green-50 text-green-700"
                    : "bg-red-50 text-red-700"
                }`}
              >

                {customer.status ===
                "active" ? (

                  <CheckCircle2
                    size={16}
                  />

                ) : (

                  <XCircle
                    size={16}
                  />

                )}

                {customer.status ===
                "active"
                  ? "Active"
                  : "Inactive"}

              </span>

            </div>

            <p className="mt-4 text-sm leading-6 text-gray-500">

              {customer.status ===
              "active"
                ? "This customer can log in and use their BanglesMart account."
                : "This customer cannot log in until the account is activated again."}

            </p>

          </section>

          {/* Customer ID */}

          <section className="rounded-xl border border-gray-200 bg-white p-5">

            <h2 className="text-sm font-medium text-gray-500">
              Customer ID
            </h2>

            <p className="mt-2 text-2xl font-semibold text-gray-900">
              #{customer.id}
            </p>

          </section>

          {/* Future Stats */}

          <section className="rounded-xl border border-gray-200 bg-white p-5">

            <h2 className="font-semibold text-gray-900">
              Customer Summary
            </h2>

            <div className="mt-5 space-y-4">

              <div className="flex items-center justify-between">

                <span className="text-sm text-gray-500">
                  Orders
                </span>

                <span className="font-medium text-gray-900">
                  0
                </span>

              </div>

              <div className="flex items-center justify-between">

                <span className="text-sm text-gray-500">
                  Total Spent
                </span>

                <span className="font-medium text-gray-900">
                  ₹0
                </span>

              </div>

              <div className="flex items-center justify-between">

                <span className="text-sm text-gray-500">
                  Addresses
                </span>

                <span className="font-medium text-gray-900">
                  0
                </span>

              </div>

            </div>

          </section>

        </div>

      </div>

    </div>
  );
}