<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('business.name') }}</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 24px auto; background: #ffffff; border-radius: 6px; overflow: hidden; }
        .header { background-color: #1a1a2e; padding: 24px 32px; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; }
        .body { padding: 32px; color: #333333; font-size: 14px; line-height: 1.6; }
        .body h2 { font-size: 18px; color: #1a1a2e; margin: 0 0 12px; }
        .body p { margin: 0 0 14px; }
        .feature-list { margin: 10px 0 0 18px; padding: 0; }
        .feature-list li { margin-bottom: 6px; }
        .button { display: inline-block; margin-top: 10px; padding: 11px 22px; background-color: #1a1a2e; color: #ffffff; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 600; }
        .footer { background-color: #f4f4f4; padding: 18px 32px; font-size: 12px; line-height: 1.6; color: #888888; text-align: center; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <h1>{{ config('business.name') }}</h1>
    </div>

    <div class="body">
        <h2>Welcome, {{ $customer->full_name }}!</h2>

        <p>
            Your account has been created successfully. You can now log in to browse our
            products, save custom designs, and place orders for collection in-store.
        </p>

        <p>Here is what you can do with your account:</p>

        <ul class="feature-list">
            <li>Save and manage your personalised product designs</li>
            <li>Add products to your wishlist for later</li>
            <li>Place orders and track their progress</li>
            <li>View your full order history</li>
        </ul>

        <a href="{{ route('catalog') }}" class="button">Browse Products</a>

        <p style="margin-top: 18px;">
            If you have any questions, feel free to reach us through the contact form on our website.
        </p>

        <p>
            Thank you for choosing {{ config('business.name') }}.
        </p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} {{ config('business.name') }}. All rights reserved.
    </div>

</div>
</body>
</html>