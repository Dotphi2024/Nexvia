<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectTitle ?? 'Your NEXVIA Verification Code' }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }
        table {
            border-spacing: 0;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        td {
            padding: 0;
        }
        img {
            border: 0;
            -ms-interpolation-mode: bicubic;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f1f5f9;
            padding: 40px 10px;
        }
        .main-container {
            max-width: 580px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            padding: 36px 32px 28px;
            text-align: center;
        }
        .header-logo {
            font-size: 26px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin: 0 0 6px 0;
        }
        .header-tagline {
            color: #93c5fd;
            font-size: 13px;
            font-weight: 500;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .content-body {
            padding: 36px 32px 32px;
        }
        .greeting {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 12px;
        }
        .description {
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
            margin: 0 0 24px;
        }
        .otp-box-wrapper {
            background: #f8fafc;
            border: 2px dashed #94a3b8;
            border-radius: 12px;
            padding: 24px 20px;
            text-align: center;
            margin: 0 0 24px;
        }
        .otp-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #64748b;
            margin-bottom: 8px;
        }
        .otp-code {
            font-family: 'Courier New', Courier, monospace, sans-serif;
            font-size: 38px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #0f172a;
            margin: 0;
            user-select: all;
        }
        .otp-expiry {
            font-size: 13px;
            color: #dc2626;
            font-weight: 600;
            margin-top: 10px;
        }
        .security-notice {
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 24px;
        }
        .security-notice p {
            margin: 0;
            font-size: 13px;
            line-height: 1.5;
            color: #92400e;
        }
        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 28px 0;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 24px 32px;
            text-align: center;
            font-size: 12px;
            line-height: 1.6;
            color: #64748b;
        }
        .footer a {
            color: #2563eb;
            text-decoration: none;
        }
        .social-links {
            margin: 12px 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td align="center">
                    <div class="main-container">
                        <!-- Header -->
                        <div class="header">
                            <div class="header-logo">NEXVIA</div>
                            <p class="header-tagline">Secure Authentication & Verification</p>
                        </div>

                        <!-- Body Content -->
                        <div class="content-body">
                            <h1 class="greeting">Hello {{ $name ?: 'Valued Customer' }},</h1>
                            <p class="description">
                                @if(isset($purpose) && $purpose === 'registration')
                                    Thank you for registering with <strong>NEXVIA</strong>. Please use the verification code below to complete your registration.
                                @elseif(isset($purpose) && $purpose === 'password_reset')
                                    We received a request to reset your <strong>NEXVIA</strong> account password. Please use the verification code below to proceed.
                                @else
                                    Please use the one-time verification code (OTP) below to authenticate your <strong>NEXVIA</strong> account.
                                @endif
                            </p>

                            <!-- OTP Box -->
                            <div class="otp-box-wrapper">
                                <div class="otp-label">Your One-Time Password (OTP)</div>
                                <div class="otp-code">{{ $otp }}</div>
                                <div class="otp-expiry">&#9201; Valid for {{ $validMinutes ?? 10 }} minutes</div>
                            </div>

                            <!-- Security Warning -->
                            <div class="security-notice">
                                <p><strong>Security Tip:</strong> Never share this code with anyone. NEXVIA representatives will never call or email you asking for your OTP or password.</p>
                            </div>

                            <p style="font-size: 13px; color: #64748b; margin: 0;">
                                If you did not request this verification code, please ignore this email or contact support if you suspect unauthorized activity.
                            </p>

                            <div class="divider"></div>

                            <p style="font-size: 14px; color: #334155; margin: 0;">
                                Warm regards,<br>
                                <strong>The NEXVIA Team</strong>
                            </p>
                        </div>

                        <!-- Footer -->
                        <div class="footer">
                            <p style="margin: 0 0 6px;">
                                This is an automated transactional security email sent from <strong>NEXVIA</strong>.
                            </p>
                            <p style="margin: 0 0 10px;">
                                Need help? Email us at <a href="mailto:{{ config('mail.from.address', 'nexviadls@gmail.com') }}">{{ config('mail.from.address', 'nexviadls@gmail.com') }}</a>
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                                &copy; {{ date('Y') }} NEXVIA. All rights reserved.
                            </p>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
