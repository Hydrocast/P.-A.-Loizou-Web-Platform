<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation #{{ $order->order_id }}</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 6px; overflow: hidden; }
        .header { background-color: #1a1a2e; padding: 32px 40px; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; }
        .header p { color: #aaaacc; margin: 6px 0 0; font-size: 14px; }
        .body { padding: 40px; color: #333333; font-size: 15px; line-height: 1.7; }
        .body h2 { font-size: 18px; color: #1a1a2e; margin-top: 0; }
        .section-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #888888; margin: 32px 0 12px; border-bottom: 1px solid #eeeeee; padding-bottom: 8px; }
        .detail-row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 14px; }
        .detail-label { color: #666666; }
        .item-meta { margin-top: 4px; font-size: 12px; line-height: 1.5; color: #777777; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        thead th { text-align: left; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #888888; padding: 8px 0; border-bottom: 2px solid #eeeeee; }
        tbody td { padding: 10px 0; font-size: 14px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
        tbody td.right { text-align: right; }
        .totals-table { margin-top: 8px; }
        .totals-table td { font-size: 14px; padding: 5px 0; }
        .totals-table .grand-total td { font-size: 16px; font-weight: 700; color: #1a1a2e; padding-top: 12px; }
        .pickup-box { margin-top: 32px; padding: 20px; background-color: #f0f4ff; border-left: 4px solid #1a1a2e; font-size: 14px; }
        .pickup-box strong { display: block; margin-bottom: 6px; color: #1a1a2e; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 13px; font-weight: 600; background-color: #e8f0fe; color: #1d4ed8; }
        .footer { background-color: #f4f4f4; padding: 24px 40px; font-size: 12px; color: #888888; text-align: center; }
    </style>
</head>
<body>
<div class="wrapper">

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

        {{-- Order meta --}}
        <div class="section-title">Order Details</div>

        <div class="detail-row">
            <span class="detail-label">Order Number</span>
            <span><strong>#{{ $order->order_id }}</strong></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Order Date</span>
            <span>{{ $order->order_creation_timestamp->format('d M Y, H:i') }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Status</span>
            <span><span class="status-badge">{{ $order->order_status->label() }}</span></span>
        </div>

        {{-- Customer contact --}}
        <div class="section-title">Contact Information</div>

        <div class="detail-row">
            <span class="detail-label">Name</span>
            <span>{{ $order->customer_name }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Email</span>
            <span>{{ $order->customer_email }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Phone</span>
            <span>{{ $order->customer_phone }}</span>
        </div>

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
                @endphp
                <tr>
                    <td>
                        <div>{{ $item->product_name }}</div>

                        @if ($shirtColorLabel || $printSidesLabel)
                            <div class="item-meta">
                                @if ($shirtColorLabel)
                                    <div>Shirt Color: {{ $shirtColorLabel }}</div>
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
            Your order will be available for collection from our Paralimni store once it is marked <strong style="display:inline; margin-bottom:0; color:#1a1a2e;">Ready for Pickup</strong>. Please bring this email or provide your order number <strong style="display:inline; margin-bottom:0; color:#1a1a2e;">#{{ $order->order_id }}</strong> when collecting your order.
        </div>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} {{ config('business.name') }}. All rights reserved.<br>
        This confirmation was sent to {{ $order->customer_email }}.
    </div>

</div>
</body>
</html>