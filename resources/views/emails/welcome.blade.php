<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to NEXVIA</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
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
        }
        .content-body {
            padding: 36px 32px 32px;
        }
        .greeting {
            font-size: 22px;
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
        .referral-card {
            background-color: #eff6ff;
            border: 1px dashed #3b82f6;
            border-radius: 12px;
            padding: 18px;
            text-align: center;
            margin-bottom: 24px;
        }
        .referral-label {
            font-size: 12px;
            font-weight: 700;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .referral-code {
            font-family: monospace;
            font-size: 22px;
            font-weight: 800;
            color: #1d4ed8;
            letter-spacing: 4px;
        }
        .features-grid {
            margin: 20px 0;
        }
        .feature-item {
            padding: 8px 0;
            font-size: 14px;
            color: #334155;
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
                            <p class="header-tagline">Welcome to the Future of Farm Equipment & DLS</p>
                        </div>

                        <!-- Content -->
                        <div class="content-body">
                            <h1 class="greeting">Welcome, {{ $customerName }}!</h1>
                            <p class="description">
                                We are thrilled to have you with us. Your <strong>NEXVIA</strong> account is officially active. You now have full access to explore farm equipment, place bookings, track deliveries, and earn rewards.
                            </p>

                            @if(!empty($referralCode))
                            <!-- Referral Card -->
                            <div class="referral-card">
                                <div class="referral-label">Your Personal Referral Code</div>
                                <div class="referral-code">{{ $referralCode }}</div>
                                <p style="font-size: 12px; color: #1e40af; margin: 6px 0 0;">
                                    Share this code with friends and earn referral bonuses on their bookings!
                                </p>
                            </div>
                            @endif

                            <div class="features-grid">
                                <div class="feature-item">&#10003; <strong>Explore Premium Machinery:</strong> Discover the latest agro-tech equipment.</div>
                                <div class="feature-item">&#10003; <strong>Hassle-Free Bookings:</strong> Secure with transparent milestone payments.</div>
                                <div class="feature-item">&#10003; <strong>Live Delivery Tracking:</strong> Real-time status updates via DSP network.</div>
                            </div>

                            <p style="font-size: 14px; color: #334155; margin: 24px 0 0;">
                                Warm regards,<br>
                                <strong>The NEXVIA Team</strong>
                            </p>
                        </div>

                        <!-- Footer -->
                        <div class="footer">
                            <p style="margin: 0 0 6px;">
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
