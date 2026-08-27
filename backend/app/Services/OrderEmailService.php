<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderEmailService
{
    public function __construct(
        private NativeMailService $mailer,
        private InvoicePdfService $invoicePdfService,
    ) {}

    public function sendConfirmation(Order $order): bool
    {
        $order->loadMissing(['user', 'items', 'invoice']);

        if (!$order->user?->email) {
            return false;
        }

        $pdf = $this->invoicePdfService->generate($order);
        $order->load('invoice');

        $html = view('emails.order-confirmation', [
            'order' => $order,
        ])->render();

        return $this->mailer->send(
            $order->user->email,
            'BanglesMart Order Confirmation - ' . $order->order_number,
            $html,
            [[
                'data' => $pdf,
                'name' => 'BanglesMart-' . ($order->invoice?->invoice_number ?? $order->order_number) . '-Invoice.pdf',
                'mime' => 'application/pdf',
            ]],
        );
    }

    public function sendStatus(Order $order): bool
    {
        $order->loadMissing(['user', 'items', 'invoice']);

        if (!$order->user?->email) {
            return false;
        }

        $html = view('emails.order-status', [
            'order' => $order,
        ])->render();

        return $this->mailer->send(
            $order->user->email,
            'BanglesMart Order Update - ' . $order->order_number,
            $html,
        );
    }
}
