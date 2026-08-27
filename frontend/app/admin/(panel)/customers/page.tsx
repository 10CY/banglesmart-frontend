"use client";

import {
  useCallback,
  useEffect,
  useState,
} from "react";

import Link from "next/link";

import {
  Search,
  UserCheck,
  UserRound,
  UserX,
  Eye,
} from "lucide-react";

import { apiFetch } from "@/lib/api";

/* -------------------------------------------------------------------------- */
/* Types                                                                      */
/* -------------------------------------------------------------------------- */

type Customer = {
  id: number;
  name: string;
  email: string;
  role: string;
  status: string;

  created_at: string;
  updated_at: string;
};

type Summary = {
  total: number;
  active: number;
  inactive: number;
};

type Pagination = {
  current_page: number;
  last_page: number;
  total: number;
};

/* -------------------------------------------------------------------------- */
/* Page                                                                       */
/* -------------------------------------------------------------------------- */

export default function CustomersPage() {
  const [
    customers,
    setCustomers,
  ] = useState<Customer[]>([]);

  const [
    summary,
    setSummary,
  ] = useState<Summary>({
    total: 0,
    active: 0,
    inactive: 0,
  });

  const [
    pagination,
    setPagination,
  ] = useState<Pagination>({
    current_page: 1,
    last_page: 1,
    total: 0,
  });

  const [
    loading,
    setLoading,
  ] = useState(true);

  const [
    search,
    setSearch,
  ] = useState("");

  const [
    status,
    setStatus,
  ] = useState("");

  const [
    page,
    setPage,
  ] = useState(1);

  /* ------------------------------------------------------------------------ */
  /* Load                                                                     */
  /* ------------------------------------------------------------------------ */

  const loadCustomers =
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

        if (status) {
          params.set(
            "status",
            status
          );
        }

        params.set(
          "page",
          String(page)
        );

        const response =
          await apiFetch(
            `/admin/customers?${params.toString()}`
          );

        const data =
          await response.json();

        if (!response.ok) {
          console.error(
            data.message ||
              "Unable to load customers."
          );

          return;
        }

        const paginator =
          data.data;

        setCustomers(
          paginator.data || []
        );

        setPagination({
          current_page:
            paginator.current_page || 1,

          last_page:
            paginator.last_page || 1,

          total:
            paginator.total || 0,
        });

        setSummary(
          data.summary || {
            total: 0,
            active: 0,
            inactive: 0,
          }
        );
      } catch (error) {
        console.error(
          "Customer error:",
          error
        );
      } finally {
        setLoading(false);
      }
    }, [
      search,
      status,
      page,
    ]);

  useEffect(() => {
    const timer =
      window.setTimeout(() => {
        loadCustomers();
      }, 300);

    return () =>
      window.clearTimeout(timer);
  }, [loadCustomers]);

  useEffect(() => {
    setPage(1);
  }, [
    search,
    status,
  ]);

  /* ------------------------------------------------------------------------ */
  /* Date                                                                     */
  /* ------------------------------------------------------------------------ */

  function formatDate(
    value: string
  ) {
    return new Intl.DateTimeFormat(
      "en-IN",
      {
        dateStyle: "medium",
      }
    ).format(
      new Date(value)
    );
  }

  /* ------------------------------------------------------------------------ */
  /* UI                                                                       */
  /* ------------------------------------------------------------------------ */

  return (
    <div>

      {/* Header */}

      <div className="mb-6">

        <h1 className="text-2xl font-semibold text-gray-900">
          Customers
        </h1>

        <p className="mt-1 text-sm text-gray-500">
          Manage BanglesMart customer
          accounts.
        </p>

      </div>

      {/* Summary */}

      <div className="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">

        {/* Total */}

        <div className="rounded-xl border border-gray-200 bg-white p-5">

          <div className="flex items-center justify-between">

            <div>
              <p className="text-sm text-gray-500">
                Total Customers
              </p>

              <p className="mt-2 text-2xl font-semibold text-gray-900">
                {summary.total}
              </p>
            </div>

            <div className="rounded-lg bg-gray-100 p-3 text-gray-700">
              <UserRound size={22} />
            </div>

          </div>

        </div>

        {/* Active */}

        <div className="rounded-xl border border-gray-200 bg-white p-5">

          <div className="flex items-center justify-between">

            <div>
              <p className="text-sm text-gray-500">
                Active
              </p>

              <p className="mt-2 text-2xl font-semibold text-gray-900">
                {summary.active}
              </p>
            </div>

            <div className="rounded-lg bg-green-50 p-3 text-green-700">
              <UserCheck size={22} />
            </div>

          </div>

        </div>

        {/* Inactive */}

        <div className="rounded-xl border border-gray-200 bg-white p-5">

          <div className="flex items-center justify-between">

            <div>
              <p className="text-sm text-gray-500">
                Inactive
              </p>

              <p className="mt-2 text-2xl font-semibold text-gray-900">
                {summary.inactive}
              </p>
            </div>

            <div className="rounded-lg bg-red-50 p-3 text-red-700">
              <UserX size={22} />
            </div>

          </div>

        </div>

      </div>

      {/* Filters */}

      <div className="mb-5 rounded-xl border border-gray-200 bg-white p-4">

        <div className="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_200px]">

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
              placeholder="Search customer name or email..."
              className="w-full rounded-lg border border-gray-300 py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none focus:border-gray-500"
            />

          </div>

          <select
            value={status}
            onChange={(event) =>
              setStatus(
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

      {/* Table */}

      <div className="overflow-hidden rounded-xl border border-gray-200 bg-white">

        <div className="overflow-x-auto">

          <table className="w-full min-w-[850px]">

            <thead className="border-b border-gray-200 bg-gray-50">

              <tr>

                <th className="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                  Customer
                </th>

                <th className="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                  Email
                </th>

                <th className="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                  Status
                </th>

                <th className="px-5 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                  Joined
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
                    colSpan={5}
                    className="px-5 py-14 text-center text-sm text-gray-500"
                  >
                    Loading customers...
                  </td>
                </tr>

              ) : customers.length === 0 ? (

                <tr>
                  <td
                    colSpan={5}
                    className="px-5 py-14 text-center"
                  >

                    <UserRound
                      size={36}
                      className="mx-auto text-gray-300"
                    />

                    <p className="mt-3 text-sm font-medium text-gray-700">
                      No customers found
                    </p>

                    <p className="mt-1 text-sm text-gray-500">
                      Registered customers
                      will appear here.
                    </p>

                  </td>
                </tr>

              ) : (

                customers.map(
                  (customer) => (

                    <tr
                      key={
                        customer.id
                      }
                      className="transition hover:bg-gray-50"
                    >

                      {/* Customer */}

                      <td className="px-5 py-4">

                        <div className="flex items-center gap-3">

                          <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100 font-medium text-gray-700">

                            {customer.name
                              ?.charAt(0)
                              .toUpperCase() ||
                              "C"}

                          </div>

                          <div>

                            <p className="font-medium text-gray-900">
                              {
                                customer.name
                              }
                            </p>

                            <p className="mt-1 text-xs text-gray-400">
                              Customer #
                              {
                                customer.id
                              }
                            </p>

                          </div>

                        </div>

                      </td>

                      {/* Email */}

                      <td className="px-5 py-4 text-sm text-gray-600">
                        {
                          customer.email
                        }
                      </td>

                      {/* Status */}

                      <td className="px-5 py-4">

                        <span
                          className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium capitalize ${
                            customer.status ===
                            "active"
                              ? "bg-green-50 text-green-700"
                              : "bg-red-50 text-red-700"
                          }`}
                        >
                          {
                            customer.status
                          }
                        </span>

                      </td>

                      {/* Joined */}

                      <td className="px-5 py-4 text-sm text-gray-500">
                        {formatDate(
                          customer.created_at
                        )}
                      </td>

                      {/* Action */}

                      <td className="px-5 py-4">

                        <div className="flex justify-end">

                          <Link
                            href={`/admin/customers/${customer.id}`}
                            title="View customer"
                            className="rounded-lg border border-gray-200 p-2 text-gray-700 transition hover:bg-gray-100"
                          >
                            <Eye
                              size={16}
                            />
                          </Link>

                        </div>

                      </td>

                    </tr>

                  )
                )

              )}

            </tbody>

          </table>

        </div>

        {/* Pagination */}

        {!loading &&
          pagination.total > 0 && (

          <div className="flex items-center justify-between border-t border-gray-200 px-5 py-4">

            <p className="text-sm text-gray-500">
              {
                pagination.total
              }{" "}
              customers
            </p>

            <div className="flex gap-2">

              <button
                type="button"
                disabled={
                  page <= 1
                }
                onClick={() =>
                  setPage(
                    Math.max(
                      1,
                      page - 1
                    )
                  )
                }
                className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 disabled:opacity-40"
              >
                Previous
              </button>

              <button
                type="button"
                disabled={
                  page >=
                  pagination.last_page
                }
                onClick={() =>
                  setPage(
                    page + 1
                  )
                }
                className="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 disabled:opacity-40"
              >
                Next
              </button>

            </div>

          </div>

        )}

      </div>

    </div>
  );
}