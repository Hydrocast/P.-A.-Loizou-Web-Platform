<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Update #{{ $order->order_id }}</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 6px; overflow: hidden; }
        .header { background-color: #1a1a2e; padding: 32px 40px; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; }
        .header p { color: #aaaacc; margin: 6px 0 0; font-size: 14px; }
        .body { padding: 40px; color: #333333; font-size: 15px; line-height: 1.7; }
        .body h2 { font-size: 18px; color: #1a1a2e; margin-top: 0; }
        .status-block { margin: 28px 0; padding: 20px 24px; background-color: #f0f4ff; border-left: 4px solid #1a1a2e; }
        .status-block .label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #888888; margin-bottom: 4px; }
        .status-block .value { font-size: 20px; font-weight: 700; color: #1a1a2e; }
        .section-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #888888; margin: 32px 0 12px; border-bottom: 1px solid #eeeeee; padding-bottom: 8px; }
        .detail-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 14px; }
        .detail-label { color: #666666; }
        .pickup-box { margin-top: 24px; padding: 16px 20px; background-color: #e8f5e9; border-left: 4px solid #2e7d32; font-size: 14px; color: #1b5e20; }
        .footer { background-color: #f4f4f4; padding: 24px 40px; font-size: 12px; color: #888888; text-align: center; }
    </style>
</head>
<body>
<div class="wrapper">

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

        {{-- Status message based on current status --}}
        @php
            $statusMessages = [
                'Pending' => 'Your order has been submitted successfully and is currently awaiting staff review.',
                'Processing' => 'Your order is now being worked on by our team.',
                'Ready for Pickup' => 'Great news — your order is ready and waiting for you at our store.',
                'Completed' => 'Your order has been completed. Thank you for your business!',
                'Cancelled' => 'Your order has been cancelled. If you believe this is an error, please contact us.',
            ];

            $statusMessage = $statusMessages[$order->order_status->value] ?? 'Your order status has been updated.';
        @endphp

        <p>{{ $statusMessage }}</p>

        @if ($order->order_status->value === 'Ready for Pickup')
        <div class="pickup-box">
            <strong>Your order is ready for collection.</strong><br>
            Please bring this email or your order number <strong>#{{ $order->order_id }}</strong>
            when you visit our store.
        </div>
        @endif

        {{-- Order summary --}}
        <div class="section-title">Order Summary</div>

        <div class="detail-row">
            <span class="detail-label">Order Number</span>
            <span><strong>#{{ $order->order_id }}</strong></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Order Date</span>
            <span>{{ $order->order_creation_timestamp->format('d M Y, H:i') }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Order Total</span>
            <span>€{{ number_format($order->total_amount, 2) }}</span>
        </div>

        <p style="margin-top: 32px; font-size: 14px; color: #666666;">
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