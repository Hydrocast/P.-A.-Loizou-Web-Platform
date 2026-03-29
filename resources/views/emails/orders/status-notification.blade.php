<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Update #{{ $order->order_id }}</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 24px auto; background: #ffffff; border-radius: 6px; overflow: hidden; }
        .header { background-color: #1a1a2e; padding: 24px 32px; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; }
        .header p { color: #aaaacc; margin: 6px 0 0; font-size: 14px; }
        .body { padding: 32px; color: #333333; font-size: 14px; line-height: 1.6; }
        .body h2 { font-size: 18px; color: #1a1a2e; margin: 0 0 12px; }
        .body p { margin: 0 0 14px; }
        .status-block { margin: 18px 0; padding: 16px 18px; background-color: #f0f4ff; border-left: 4px solid #1a1a2e; }
        .status-block .label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #888888; margin-bottom: 4px; }
        .status-block .value { font-size: 18px; font-weight: 700; color: #1a1a2e; }
        .section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #888888; margin: 24px 0 10px; border-bottom: 1px solid #eeeeee; padding-bottom: 6px; }
        .detail-table { width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 14px; }
        .detail-table td { padding: 2px 0; vertical-align: top; }
        .detail-label { color: #666666; width: 140px; }
        .pickup-box { margin-top: 16px; padding: 14px 16px; background-color: #e8f5e9; border-left: 4px solid #2e7d32; font-size: 13px; line-height: 1.6; color: #1b5e20; }
        .cta-wrap { margin-top: 16px; }
        .button { display: inline-block; padding: 11px 22px; background-color: #1a1a2e; color: #ffffff; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; }
        .helper-text { margin-top: 10px; font-size: 12px; color: #777777; }
        .footer { background-color: #f4f4f4; padding: 18px 32px; font-size: 12px; line-height: 1.6; color: #888888; text-align: center; }
    </style>
</head>
<body>
<div class="wrapper">

    @php
        $ordersUrl = route('account.orders');

        $statusMessages = [
            'Pending' => 'Your order has been submitted successfully and is currently awaiting staff review.',
            'Processing' => 'Your order is now being worked on by our team.',
            'Ready for Pickup' => 'Great news — your order is ready and waiting for you at our store.',
            'Completed' => 'Your order has been completed. Thank you for your business!',
            'Cancelled' => 'Your order has been cancelled. If you believe this is an error, please contact us.',
        ];

        $statusMessage = $statusMessages[$order->order_status->value] ?? 'Your order status has been updated.';
    @endphp

    <div class="header">
        <h1>{{ config('business.name') }}</h1>
        <p>Order Status Update</p>
    </div>

    <div class="body">
        <h2>Hi {{ $order->customer_name }},</h2>

        <p>The status of your order <strong>#{{ $order->order_id }}</strong> has been updated.</p>

        <div class="status-block">
            <div class="label">Current Status</div>
            <div class="value">{{ $order->order_status->label() }}</div>
        </div>

        <p>{{ $statusMessage }}</p>

        <div class="cta-wrap">
                <a
                    href="{{ $ordersUrl }}"
                    class="button"
                    style="display:inline-block; padding:11px 22px; background-color:#1a1a2e; text-decoration:none; border-radius:4px;"
                >
                    <span style="color:#ffffff; text-decoration:none; font-size:14px; font-weight:600;">
                        View Order History
                    </span>
                </a>
            <div class="helper-text">
                Open your account to review this order and any previous orders.
            </div>
        </div>

        @if ($order->order_status->value === 'Ready for Pickup')
        <div class="pickup-box">
            <strong>Your order is ready for collection.</strong><br>
            Please bring this email or your order number <strong>#{{ $order->order_id }}</strong>
            when you visit our store.
        </div>
        @endif

        {{-- Order summary --}}
        <div class="section-title">Order Summary</div>

        <table class="detail-table">
            <tr>
                <td class="detail-label">Order Number</td>
                <td style="text-align:right"><strong>#{{ $order->order_id }}</strong></td>
            </tr>
            <tr>
                <td class="detail-label">Order Date</td>
                <td style="text-align:right">{{ $order->order_creation_timestamp->format('d M Y, H:i') }}</td>
            </tr>
            <tr>
                <td class="detail-label">Order Total</td>
                <td style="text-align:right">€{{ number_format($order->total_amount, 2) }}</td>
            </tr>
        </table>

        <p style="margin-top: 20px; font-size: 13px; color: #666666;">
            If you have any questions about your order, please contact us through
            the contact form on our website and reference your order number.
        </p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} {{ config('business.name') }}. All rights reserved.<br>
        This notification was sent to {{ $order->customer_email }}.
    </div>

</div>
</body>
</html>