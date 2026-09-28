<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXVIA Booking Confirmation</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }
        table {
            border-spacing: 0;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        td {
            padding: 0;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f1f5f9;
            padding: 40px 10px;
        }
        .main-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #065f46 0%, #047857 100%);
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
            color: #a7f3d0;
            font-size: 13px;
            font-weight: 500;
            margin: 0;
        }
        .content-body {
            padding: 36px 32px 32px;
        }
        .success-badge {
            display: inline-block;
            background-color: #d1fae5;
            color: #065f46;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
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
        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        .summary-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .summary-label {
            color: #64748b;
            font-weight: 500;
            width: 40%;
        }
        .summary-value {
            color: #0f172a;
            font-weight: 600;
            width: 60%;
            text-align: right;
        }
        .amount-highlight {
            font-size: 18px;
            color: #047857;
            font-weight: 800;
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
            color: #047857;
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
                            <p class="header-tagline">Official Booking Confirmation</p>
                        </div>

                        <!-- Content -->
                        <div class="content-body">
                            <div class="success-badge">&#10003; Booking Confirmed</div>
                            <h1 class="greeting">Thank You, {{ $customerName }}!</h1>
                            <p class="description">
                                Your booking with <strong>NEXVIA</strong> has been successfully placed. Below are your booking and payment details.
                            </p>

                            <!-- Summary Card -->
                            <div class="summary-card">
                                <table width="100%" cellpadding="8" cellspacing="0">
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td style="color: #64748b; font-size: 13px;">Booking Reference</td>
                                        <td align="right" style="color: #0f172a; font-weight: 700; font-size: 14px;">{{ $bookingNumber }}</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td style="color: #64748b; font-size: 13px;">Item / Product</td>
                                        <td align="right" style="color: #0f172a; font-weight: 600; font-size: 14px;">{{ $productName }} (x{{ $quantity }})</td>
                                    </tr>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td style="color: #64748b; font-size: 13px;">Amount Paid</td>
                                        <td align="right" style="color: #047857; font-weight: 800; font-size: 16px;">&#8377;{{ number_format((float)$amountPaid, 2) }}</td>
                                    </tr>
                                    @if(isset($balanceAmount) && $balanceAmount > 0)
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td style="color: #64748b; font-size: 13px;">Balance Remaining</td>
                                        <td align="right" style="color: #dc2626; font-weight: 700; font-size: 14px;">&#8377;{{ number_format((float)$balanceAmount, 2) }}</td>
                                    </tr>
                                    @endif
                                    @if(!empty($dueDate))
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td style="color: #64748b; font-size: 13px;">Balance Due Date</td>
                                        <td align="right" style="color: #0f172a; font-weight: 600; font-size: 13px;">{{ $dueDate }}</td>
                                    </tr>
                                    @endif
                                    @if(!empty($shippingAddress))
                                    <tr>
                                        <td style="color: #64748b; font-size: 13px; vertical-align: top;">Delivery Address</td>
                                        <td align="right" style="color: #334155; font-size: 13px;">{{ $shippingAddress }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>

                            <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 20px;">
                                You can track the progress of your booking through the NEXVIA mobile app or web portal at any time. Our logistics partner will be assigned soon.
                            </p>

                            <p style="font-size: 14px; color: #334155; margin: 0;">
                                Warm regards,<br>
                                <strong>The NEXVIA Team</strong>
                            </p>
                        </div>

                        <!-- Footer -->
                        <div class="footer">
                            <p style="margin: 0 0 6px;">
                                Questions about this booking? Contact us at <a href="mailto:{{ config('mail.from.address', 'nexviadls@gmail.com') }}">{{ config('mail.from.address', 'nexviadls@gmail.com') }}</a>
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
