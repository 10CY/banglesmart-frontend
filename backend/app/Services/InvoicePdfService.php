<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\Schema;

class InvoicePdfService
{
    public function generate(Order $order): string
    {
        $order->loadMissing(['items', 'user']);

        $invoiceNumber = 'BM-INV-' . now()->format('Y') . '-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);
        $issuedAt = now();

        if (Schema::hasTable('invoices')) {
            $invoice = Invoice::firstOrCreate(
                ['order_id' => $order->id],
                [
                    'invoice_number' => $invoiceNumber,
                    'issued_at' => $issuedAt,
                    'status' => 'issued',
                ]
            );

            $invoiceNumber = $invoice->invoice_number;
            $issuedAt = $invoice->issued_at ?: $issuedAt;
        }

        $lines = [];
        $lines[] = ['text' => 'BANGLESMART', 'size' => 22, 'bold' => true];
        $lines[] = ['text' => 'Premium Bangles & Jewellery', 'size' => 10];
        $lines[] = ['text' => 'INVOICE', 'size' => 16, 'bold' => true];
        $lines[] = ['text' => 'Invoice No: ' . $invoiceNumber, 'size' => 10];
        $lines[] = ['text' => 'Order No: ' . $order->order_number, 'size' => 10];
        $lines[] = ['text' => 'Invoice Date: ' . $issuedAt->format('d M Y'), 'size' => 10];
        $lines[] = ['text' => ''];
        $lines[] = ['text' => 'BILL TO', 'size' => 11, 'bold' => true];
        $lines[] = ['text' => (string) ($order->user?->name ?? data_get($order->shipping_address, 'full_name', 'Customer')), 'size' => 10];
        $lines[] = ['text' => (string) ($order->user?->email ?? ''), 'size' => 9];
        $lines[] = ['text' => 'Phone: ' . (string) data_get($order->shipping_address, 'phone', ''), 'size' => 9];
        $lines[] = ['text' => $this->addressLine($order->shipping_address), 'size' => 9];
        $lines[] = ['text' => ''];
        $lines[] = ['text' => 'ITEMS', 'size' => 11, 'bold' => true];

        foreach ($order->items as $item) {
            $name = $item->product_name . ' x' . $item->quantity;
            $variant = trim(implode(' / ', array_filter([$item->size_name, $item->color_name, $item->variant_sku])));
            if ($variant !== '') {
                $name .= ' (' . $variant . ')';
            }
            $lines[] = ['text' => $this->truncate($name, 72) . '    Rs. ' . number_format((float) $item->line_total, 2), 'size' => 9];
        }

        $lines[] = ['text' => ''];
        $lines[] = ['text' => 'Subtotal: Rs. ' . number_format((float) $order->subtotal, 2), 'size' => 10];
        $lines[] = ['text' => 'Shipping: Rs. ' . number_format((float) $order->shipping_amount, 2), 'size' => 10];
        $lines[] = ['text' => 'Discount: Rs. ' . number_format((float) $order->discount_amount, 2), 'size' => 10];
        $lines[] = ['text' => 'TOTAL: Rs. ' . number_format((float) $order->total_amount, 2), 'size' => 13, 'bold' => true];
        $lines[] = ['text' => 'Payment: ' . strtoupper((string) $order->payment_method) . ' / ' . strtoupper((string) $order->payment_status), 'size' => 9];
        $lines[] = ['text' => ''];
        $lines[] = ['text' => 'Thank you for shopping with BanglesMart.', 'size' => 10, 'bold' => true];
        $lines[] = ['text' => 'This is a computer-generated invoice.', 'size' => 8];

        return $this->buildPdf($lines);
    }

    private function addressLine(?array $address): string
    {
        $parts = array_filter([
            data_get($address, 'address_line_1'),
            data_get($address, 'address_line_2'),
            data_get($address, 'landmark'),
            data_get($address, 'city'),
            data_get($address, 'state'),
            data_get($address, 'postal_code'),
            data_get($address, 'country'),
        ]);

        return implode(', ', $parts);
    }

    private function truncate(string $text, int $length): string
    {
        return mb_strlen($text) > $length ? mb_substr($text, 0, $length - 3) . '...' : $text;
    }

    private function buildPdf(array $lines): string
    {
        $width = 595;
        $height = 842;
        $content = "q\n0.56 0.03 0.16 rg\n40 785 515 2 re f\n0 0 0 rg\n";
        $y = 750;

        foreach ($lines as $line) {
            $text = (string) ($line['text'] ?? '');
            if ($text === '') {
                $y -= 12;
                continue;
            }

            $size = (int) ($line['size'] ?? 10);
            $font = !empty($line['bold']) ? '/F2' : '/F1';
            $safe = $this->pdfEscape($text);
            $content .= "BT {$font} {$size} Tf 45 {$y} Td ({$safe}) Tj ET\n";
            $y -= max(16, $size + 6);

            if ($y < 55) {
                break;
            }
        }

        $content .= "Q\n";

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . $width . ' ' . $height . '] /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >>',
            '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . 'endstream',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>',
        ];

        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $offsets = [0];

        foreach ($objects as $index => $object) {
            $number = $index + 1;
            $offsets[$number] = strlen($pdf);
            $pdf .= $number . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    private function pdfEscape(string $value): string
    {
        $value = preg_replace('/[^\x20-\x7E]/', '?', $value) ?? $value;
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ' '], $value);
    }
}
