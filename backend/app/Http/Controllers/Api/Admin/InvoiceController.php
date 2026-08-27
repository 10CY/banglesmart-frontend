<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\InvoicePdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function download(Request $request, Order $order, InvoicePdfService $pdfService)
    {
        try {
            $order->load(['items', 'user']);
            $pdf = $pdfService->generate($order);
            $invoiceNumber = 'BM-INV-' . now()->format('Y') . '-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);

            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $invoiceNumber . '.pdf"',
                'Cache-Control' => 'private, no-store, max-age=0',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Admin invoice generation failed.', [
                'order_id' => $order->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'We could not generate the invoice right now. Please try again.',
            ], 500);
        }
    }
}
