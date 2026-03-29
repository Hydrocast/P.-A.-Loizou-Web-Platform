<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation #{{ $order->order_id }}</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 24px auto; background: #ffffff; border-radius: 6px; overflow: hidden; }
        .header { background-color: #1a1a2e; padding: 24px 32px; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; }
        .header p { color: #aaaacc; margin: 6px 0 0; font-size: 14px; }
        .body { padding: 32px; color: #333333; font-size: 14px; line-height: 1.6; }
        .body h2 { font-size: 18px; color: #1a1a2e; margin: 0 0 12px; }
        .body p { margin: 0 0 14px; }
        .section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #888888; margin: 24px 0 10px; border-bottom: 1px solid #eeeeee; padding-bottom: 6px; }
        .detail-table { width: 100%; border-collapse: collapse; margin-bottom: 0; font-size: 14px; }
        .detail-table td { padding: 2px 0; vertical-align: top; }
        .detail-label { color: #666666; width: 140px; }
        .item-meta { margin-top: 4px; font-size: 12px; line-height: 1.4; color: #777777; }
        table { width: 100%; border-collapse: collapse; margin-top: 2px; }
        thead th { text-align: left; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #888888; padding: 7px 0; border-bottom: 2px solid #eeeeee; }
        tbody td { padding: 8px 0; font-size: 14px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
        tbody td.right { text-align: right; }
        .totals-table { margin-top: 4px; }
        .totals-table td { font-size: 14px; padding: 4px 0; }
        .totals-table .grand-total td { font-size: 15px; font-weight: 700; color: #1a1a2e; padding-top: 8px; }
        .pickup-box { margin-top: 20px; padding: 16px 18px; background-color: #f0f4ff; border-left: 4px solid #1a1a2e; font-size: 13px; line-height: 1.6; }
        .pickup-box strong { display: block; margin-bottom: 4px; color: #1a1a2e; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; background-color: #e8f0fe; color: #1d4ed8; }
        .cta-wrap { margin-top: 18px; }
        .button { display: inline-block; padding: 11px 22px; background-color: #1a1a2e; color: #ffffff; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; }
        .helper-text { margin-top: 10px; font-size: 12px; color: #777777; }
        .footer { background-color: #f4f4f4; padding: 18px 32px; font-size: 12px; line-height: 1.6; color: #888888; text-align: center; }
    </style>
</head>
<body>
<div class="wrapper">

    @php
        $ordersUrl = route('account.orders');
    @endphp

    <div class="header">
        <h1>{{ config('business.name') }}</h1>
        <p>Order Confirmation</p>
    </div>

    <div class="body">
        <h2>Thank you, {{ $order->customer_name }}!</h2>

        <p>
            Your order has been placed successfully and is currently awaiting staff review.
            We will email you again when its status changes.
        </p>

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
                You can review your submitted orders at any time from your account.
            </div>
        </div>

        {{-- Order meta --}}
        <div class="section-title">Order Details</div>

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
                <td class="detail-label">Status</td>
                <td style="text-align:right"><span class="status-badge">{{ $order->order_status->label() }}</span></td>
            </tr>
        </table>

        {{-- Customer contact --}}
        <div class="section-title">Contact Information</div>

        <table class="detail-table">
            <tr>
                <td class="detail-label">Name</td>
                <td style="text-align:right">{{ $order->customer_name }}</td>
            </tr>
            <tr>
                <td class="detail-label">Email</td>
                <td style="text-align:right">{{ $order->customer_email }}</td>
            </tr>
            <tr>
                <td class="detail-label">Phone</td>
                <td style="text-align:right">{{ $order->customer_phone }}</td>
            </tr>
        </table>

        {{-- Order items --}}
        <div class="section-title">Items Ordered</div>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th class="right" style="text-align:right">Qty</th>
                    <th class="right" style="text-align:right">Unit Price</th>
                    <th class="right" style="text-align:right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                @php
                    $shirtColorLabel = \App\Support\DesignDocument::extractShirtColorLabel($item->design_snapshot);
                    $printSidesLabel = \App\Support\DesignDocument::extractPrintSidesLabel($item->design_snapshot);
                    $sizeLabel = \App\Support\DesignDocument::extractSizeLabel($item->design_snapshot);
                @endphp
                <tr>
                    <td>
                        <div>{{ $item->product_name }}</div>

                        @if ($shirtColorLabel || $printSidesLabel || $sizeLabel)
                            <div class="item-meta">
                                @if ($shirtColorLabel)
                                    <div>Shirt Color: {{ $shirtColorLabel }}</div>
                                @endif

                                @if ($sizeLabel)
                                    <div>Size: {{ $sizeLabel }}</div>
                                @endif

                                @if ($printSidesLabel)
                                    <div>Print Sides: {{ $printSidesLabel }}</div>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">€{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">€{{ number_format($item->line_subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pricing summary --}}
        <div class="section-title">Pricing Summary</div>

        <table class="totals-table">
            <tbody>
                <tr>
                    <td style="color:#666666">Net Amount (excl. VAT)</td>
                    <td style="text-align:right">€{{ number_format($order->net_amount, 2) }}</td>
                </tr>
                <tr>
                    <td style="color:#666666">VAT ({{ number_format($order->vat_rate, 0) }}%)</td>
                    <td style="text-align:right">€{{ number_format($order->vat_amount, 2) }}</td>
                </tr>
                <tr class="grand-total">
                    <td>Total (incl. VAT)</td>
                    <td style="text-align:right">€{{ number_format($order->total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Pickup instructions --}}
        <div class="pickup-box">
            <strong>In-Store Pickup</strong>
            Your order will be available for collection from our Paralimni store once it is marked
            <strong style="display:inline; margin-bottom:0; color:#1a1a2e;">Ready for Pickup</strong>.
            Please bring this email or provide your order number
            <strong style="display:inline; margin-bottom:0; color:#1a1a2e;">#{{ $order->order_id }}</strong>
            when collecting your order.
        </div>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} {{ config('business.name') }}. All rights reserved.<br>
        This confirmation was sent to {{ $order->customer_email }}.
    </div>

</div>
</body>
</html>