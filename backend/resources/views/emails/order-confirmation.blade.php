<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Order Confirmation</title>
</head>
<body style="margin:0;background:#f7f4ee;font-family:Arial,Helvetica,sans-serif;color:#222;">
<div style="max-width:620px;margin:30px auto;background:#fff;border:1px solid #e8e0d4;border-radius:18px;overflow:hidden;">
    <div style="padding:28px 30px;background:#171717;color:#fff;">
        <div style="font-size:22px;font-weight:700;letter-spacing:.08em;">BANGLESMART</div>
        <div style="margin-top:7px;color:#d8b75b;font-size:12px;letter-spacing:.12em;text-transform:uppercase;">Premium Bangles & Jewellery</div>
    </div>
    <div style="padding:32px;">
        <div style="display:inline-block;padding:7px 12px;border-radius:999px;background:#f6efe2;color:#8f0828;font-size:12px;font-weight:700;">ORDER CONFIRMED</div>
        <h1 style="font-size:28px;margin:18px 0 10px;">Thank you, {{ $order->user?->name ?? 'Customer' }}!</h1>
        <p style="color:#666;line-height:1.7;">Your order <strong>{{ $order->order_number }}</strong> has been received successfully. Your invoice is attached to this email.</p>
        <div style="margin:24px 0;padding:18px;background:#fbfaf7;border:1px solid #eee6da;border-radius:14px;">
            <div style="font-size:12px;color:#888;text-transform:uppercase;letter-spacing:.1em;">Order Total</div>
            <div style="margin-top:6px;font-size:24px;font-weight:700;">₹{{ number_format((float)$order->total_amount, 2) }}</div>
        </div>
        <p style="color:#666;line-height:1.7;">Please keep the attached invoice for your records. You can also view your order anytime from your BanglesMart account.</p>
        <a href="{{ rtrim(config('app.url'), '/') }}/account/orders/{{ $order->id }}" style="display:inline-block;margin-top:10px;padding:13px 22px;border-radius:999px;background:#111827;color:#fff;text-decoration:none;font-size:14px;font-weight:600;">View My Order</a>
    </div>
    <div style="padding:20px 30px;border-top:1px solid #eee6da;color:#999;font-size:11px;">BanglesMart · Premium Bangles & Jewellery</div>
</div>
</body>
</html>
