<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your NYFEW tickets are ready</title>
</head>
<body style="margin:0;padding:0;background:#0c0c0b;font-family:Arial,Helvetica,sans-serif;color:#eee8d9;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#0c0c0b;">
        <tr><td align="center" style="padding:30px 14px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:590px;background:#171816;border:1px solid #5b5038;border-radius:16px;">
                <tr><td align="center" style="padding:32px 26px 14px;">
                    <img src="{{ $message->embed(public_path('images/nyfew-email-logo.png')) }}"
                         alt="NYFEW logo" width="92" height="92"
                         style="display:block;width:92px;height:92px;object-fit:contain;border:0;">
                    <div style="padding-top:14px;color:#d7b768;font-size:13px;font-weight:bold;letter-spacing:3px;">NYFEW TICKETS</div>
                </td></tr>
                <tr><td style="padding:8px 30px 30px;">
                    <h1 style="margin:12px 0 20px;color:#f4df96;font-size:26px;line-height:1.2;">Your tickets are ready</h1>
                    <p style="margin:0 0 16px;line-height:1.7;">Hello {{ $buyerName }},</p>
                    <p style="margin:0 0 16px;line-height:1.7;">Thank you for purchasing tickets for <strong>{{ $eventTitle }}</strong>.</p>
                    <p style="margin:0 0 16px;line-height:1.7;">Your order reference is <strong>{{ $reference }}</strong>.</p>
                    <p style="margin:0 0 22px;line-height:1.7;">Use the secure link below to view and download each guest's admission pass. Your link is valid for <strong>24 hours</strong> and can be reopened during that period. Please keep it private.</p>
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">
                        <tr><td bgcolor="#d7b768" style="border-radius:8px;">
                            <a href="{{ $accessUrl }}" style="display:inline-block;background:#d7b768;color:#16120b;text-decoration:none;padding:15px 22px;border-radius:8px;font-weight:bold;">View and download tickets</a>
                        </td></tr>
                    </table>
                    <p style="margin:0 0 14px;font-size:13px;color:#c5bda8;line-height:1.7;">If the link expires, <a style="color:#f4df96;" href="{{ route('ticket-recovery.index') }}">request another access link</a> using your purchase email and order reference.</p>
                    <p style="margin:0 0 22px;font-size:13px;color:#c5bda8;line-height:1.7;">Please keep your QR tickets private. This email is not an admission pass.</p>
                    <div style="border-top:1px solid #665334;padding-top:20px;margin-top:25px;color:#f4df96;font-weight:bold;">ProjectMakeMe Team</div>
                    <div style="padding-top:6px;font-size:12px;color:#a99e88;">NYFEW Grand Finale · Official ticketing</div>
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>
