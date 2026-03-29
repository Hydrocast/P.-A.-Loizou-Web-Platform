<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form: {{ $contactSubject }}</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 24px auto; background: #ffffff; border-radius: 6px; overflow: hidden; }
        .header { background-color: #1a1a2e; padding: 24px 32px; }
        .header h1 { color: #ffffff; margin: 0; font-size: 22px; font-weight: 600; }
        .header p { color: #aaaacc; margin: 6px 0 0; font-size: 14px; }
        .body { padding: 32px; color: #333333; font-size: 14px; line-height: 1.6; }
        .section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #888888; margin: 0 0 10px; border-bottom: 1px solid #eeeeee; padding-bottom: 6px; }
        .detail-row { display: flex; margin-bottom: 6px; font-size: 14px; }
        .detail-label { width: 90px; flex-shrink: 0; color: #666666; font-weight: 600; }
        .detail-value { color: #333333; }
        .message-box { margin-top: 20px; }
        .message-content { margin-top: 10px; padding: 16px; background-color: #f8f8f8; border: 1px solid #e8e8e8; border-radius: 4px; font-size: 14px; line-height: 1.7; white-space: pre-wrap; color: #333333; }
        .reply-notice { margin-top: 20px; padding: 14px 16px; background-color: #f0f4ff; border-left: 4px solid #1a1a2e; font-size: 13px; line-height: 1.6; color: #555555; }
        .footer { background-color: #f4f4f4; padding: 18px 32px; font-size: 12px; line-height: 1.6; color: #888888; text-align: center; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <h1>{{ config('business.name') }}</h1>
        <p>New Contact Form Submission</p>
    </div>

    <div class="body">

        <div class="section-title">Sender Details</div>

        <div class="detail-row">
            <span class="detail-label">Name</span>
            <span class="detail-value">{{ $senderName }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Email</span>
            <span class="detail-value">{{ $senderEmail }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Subject</span>
            <span class="detail-value">{{ $contactSubject }}</span>
        </div>

        <div class="message-box">
            <div class="section-title">Message</div>
            <div class="message-content">{{ $bodyMessage }}</div>
        </div>

        <div class="reply-notice">
            To reply to this enquiry, simply reply to this email. Your reply will be
            sent directly to <strong>{{ $senderEmail }}</strong>.
        </div>

    </div>

    <div class="footer">
        &copy; {{ date('Y') }} {{ config('business.name') }}.<br>
        This message was submitted via the contact form on your website.
    </div>

</div>
</body>
</html>