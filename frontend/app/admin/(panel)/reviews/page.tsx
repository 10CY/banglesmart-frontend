"use client";

import { useCallback, useEffect, useState } from "react";
import { Check, Trash2, X } from "lucide-react";
import { apiFetch } from "@/lib/api";

type Review = {
  id: number;
  rating: number;
  title: string | null;
  comment: string | null;
  status: "pending" | "approved" | "rejected";
  product?: { id: number; name: string; slug: string };
  user?: { id: number; name: string; email: string };
  created_at: string;
};
type Paginator = { data: Review[]; current_page: number; last_page: number; total: number };

export default function AdminReviewsPage() {
  const [reviews, setReviews] = useState<Review[]>([]);
  const [status, setStatus] = useState("");
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [page, setPage] = useState(1);
  const [pagination, setPagination] = useState<Paginator>({ data: [], current_page: 1, last_page: 1, total: 0 });

  const loadReviews = useCallback(async () => {
    try {
      setLoading(true);
      setError("");
      const query = new URLSearchParams({ page: String(page) });
      if (status) query.set("status", status);
      const response = await apiFetch(`/admin/reviews?${query.toString()}`);
      const json = await response.json();
      if (!response.ok) throw new Error(json?.message || "Unable to load reviews.");
      const data = json.data as Paginator;
      setReviews(data.data || []);
      setPagination(data);
    } catch (exception) {
      setError(exception instanceof Error ? exception.message : "Unable to load reviews.");
    } finally {
      setLoading(false);
    }
  }, [page, status]);

  useEffect(() => { void loadReviews(); }, [loadReviews]);

  async function updateReview(review: Review, nextStatus: Review["status"]) {
    const response = await apiFetch(`/admin/reviews/${review.id}`, { method: "PUT", body: JSON.stringify({ status: nextStatus }) });
    const json = await response.json();
    if (!response.ok) { setError(json?.message || "Unable to update review."); return; }
    await loadReviews();
  }

  async function deleteReview(review: Review) {
    if (!window.confirm("Delete this review permanently?")) return;
    const response = await apiFetch(`/admin/reviews/${review.id}`, { method: "DELETE" });
    const json = await response.json();
    if (!response.ok) { setError(json?.message || "Unable to delete review."); return; }
    await loadReviews();
  }

  return (
    <div>
      <div className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p className="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#c9a227]">Customer voice</p><h1 className="mt-2 text-3xl font-semibold text-gray-900">Reviews</h1><p className="mt-1 text-sm text-gray-500">Moderate customer feedback before it appears on the storefront.</p></div>
        <select value={status} onChange={(e) => { setStatus(e.target.value); setPage(1); }} className="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm"><option value="">All Reviews</option><option value="pending">Pending</option><option value="approved">Approved</option><option value="rejected">Rejected</option></select>
      </div>
      {error && <div className="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{error}</div>}
      <div className="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white">
        {loading ? <div className="p-8 text-sm text-gray-500">Loading reviews...</div> : reviews.length === 0 ? <div className="p-12 text-center text-sm text-gray-500">No reviews found.</div> : <div className="divide-y divide-gray-100">{reviews.map((review) => <article key={review.id} className="p-5 sm:p-6"><div className="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between"><div className="min-w-0"><div className="flex flex-wrap items-center gap-3"><span className="text-[#c9a227]">{"★".repeat(review.rating)}{"☆".repeat(5-review.rating)}</span><Status status={review.status} /></div><h2 className="mt-3 font-semibold text-gray-900">{review.title || "Customer review"}</h2><p className="mt-1 text-xs text-gray-500">{review.product?.name || "Product"} · {review.user?.name || "Customer"} · {review.user?.email || ""}</p>{review.comment && <p className="mt-4 max-w-3xl text-sm leading-6 text-gray-600">{review.comment}</p>}</div><div className="flex shrink-0 flex-wrap gap-2">{review.status !== "approved" && <button type="button" onClick={() => void updateReview(review, "approved")} className="inline-flex items-center gap-1.5 rounded-lg bg-green-50 px-3 py-2 text-xs font-semibold text-green-700"><Check size={14} /> Approve</button>}{review.status !== "rejected" && <button type="button" onClick={() => void updateReview(review, "rejected")} className="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700"><X size={14} /> Reject</button>}<button type="button" onClick={() => void deleteReview(review)} className="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-700"><Trash2 size={14} /> Delete</button></div></div></article>)}</div>}
      </div>
      {pagination.last_page > 1 && <div className="mt-5 flex items-center justify-center gap-4"><button type="button" disabled={page <= 1} onClick={() => setPage((value) => value - 1)} className="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm disabled:opacity-40">Previous</button><span className="text-sm text-gray-500">Page {pagination.current_page} of {pagination.last_page}</span><button type="button" disabled={page >= pagination.last_page} onClick={() => setPage((value) => value + 1)} className="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm disabled:opacity-40">Next</button></div>}
    </div>
  );
}

function Status({ status }: { status: Review["status"] }) {
  const classes = status === "approved" ? "bg-green-50 text-green-700" : status === "rejected" ? "bg-red-50 text-red-700" : "bg-amber-50 text-amber-700";
  return <span className={`rounded-full px-2.5 py-1 text-[10px] font-semibold capitalize ${classes}`}>{status}</span>;
}
