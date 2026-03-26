<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 6px; overflow: hidden; }
        .header { background-color: #1a1a2e; padding: 32px 40px; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; }
        .body { padding: 40px; color: #333333; font-size: 15px; line-height: 1.7; }
        .body h2 { font-size: 18px; color: #1a1a2e; margin-top: 0; }
        .button { display: inline-block; margin-top: 24px; padding: 12px 28px; background-color: #1a1a2e; color: #ffffff; text-decoration: none; border-radius: 4px; font-size: 15px; }
        .expiry-notice { margin-top: 24px; padding: 16px; background-color: #fff8e1; border-left: 4px solid #f59e0b; font-size: 14px; color: #555555; }
        .url-fallback { margin-top: 20px; font-size: 13px; color: #888888; word-break: break-all; }
        .footer { background-color: #f4f4f4; padding: 24px 40px; font-size: 12px; color: #888888; text-align: center; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <h1>{{ config('business.name') }}</h1>
    </div>

    <div class="body">
        <h2>Reset Your Password</h2>

        <p>
            We received a request to reset the password for the account associated
            with <strong>{{ $email }}</strong>.
        </p>

        <p>
            Click the button below to set a new password. If you did not request
            a password reset, you can safely ignore this email — your password
            will not change.
        </p>

        {{-- The reset URL requires both token and email for validation. --}}
        @php
            $resetUrl = url(route('customer.password.reset', [
                'token' => $token,
                'email' => $email,
            ], false));
        @endphp

        <a href="{{ $resetUrl }}" class="button">Reset Password</a>

        <div class="expiry-notice">
            This link will expire in <strong>60 minutes</strong>. After that, you will need to
            request a new reset link.
        </div>

        <div class="url-fallback">
            If the button above does not work, copy and paste this URL into your browser:<br>
            {{ $resetUrl }}
        </div>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} {{ config('business.name') }}. All rights reserved.<br>
        You received this email because a password reset was requested for your account.
    </div>

</div>
</body>
</html>