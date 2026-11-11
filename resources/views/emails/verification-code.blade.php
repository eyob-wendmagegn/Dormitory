<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Woldia University Dormitory Verification Code</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f7f6; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color:#1e293b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f4f7f6; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:480px; background-color:#ffffff; border-radius:16px; overflow:hidden; border:1px solid #e2e8f0; box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color:#0f172a; padding:24px 28px; text-align:center;">
                            <div style="font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#10b981;">Woldia University</div>
                            <div style="font-size:18px; font-weight:700; color:#ffffff; margin-top:4px;">Dormitory Management System</div>
                        </td>
                    </tr>
                    <!-- Body -->
                    <tr>
                        <td style="padding:28px;">
                            <h1 style="margin:0 0 12px; font-size:20px; font-weight:700; color:#0f172a;">
                                {{ $type === 'reset' ? 'Password Reset Verification' : 'Account Verification Code' }}
                            </h1>
                            <p style="margin:0 0 20px; font-size:14px; line-height:1.6; color:#475569;">
                                Hello{{ $name ? ' ' . e($name) : '' }},<br>
                                {{ $type === 'reset' ? 'Use the 6-digit security code below to verify your identity and reset your password:' : 'Use the 6-digit security code below to verify your email address and activate your dormitory account:' }}
                            </p>

                            <!-- Verification Code Box -->
                            <div style="background-color:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:18px; text-align:center; margin-bottom:20px;">
                                <div style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:1px; color:#15803d; margin-bottom:6px;">
                                    6-Digit Security Code
                                </div>
                                <div style="font-family:'Courier New', Courier, monospace; font-size:32px; font-weight:800; letter-spacing:8px; color:#0f172a;">
                                    {{ $code }}
                                </div>
                            </div>

                            <p style="margin:0 0 12px; font-size:13px; line-height:1.5; color:#64748b;">
                                This verification code is valid for <strong>15 minutes</strong>. Do not share this code with anyone.
                            </p>
                            <p style="margin:0; font-size:12px; line-height:1.5; color:#94a3b8;">
                                If you did not request this verification code, please ignore this email or contact campus dormitory administration.
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f8fafc; padding:16px 28px; text-align:center; border-top:1px solid #e2e8f0; font-size:11px; color:#94a3b8;">
                            Woldia University • Open Mind, Open Eyes
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
