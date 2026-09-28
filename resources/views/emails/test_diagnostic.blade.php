<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXVIA System Verification & Mail Delivery Test</title>
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
            padding: 32px 28px 24px;
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
            padding: 32px 28px;
        }
        .status-badge {
            display: inline-block;
            background-color: #dcfce7;
            color: #15803d;
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
            margin: 0 0 10px;
        }
        .description {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
            margin: 0 0 20px;
        }
        .diag-table {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .diag-table td {
            padding: 10px 14px;
            font-size: 13px;
            border-bottom: 1px solid #e2e8f0;
        }
        .diag-table tr:last-child td {
            border-bottom: none;
        }
        .label {
            color: #64748b;
            font-weight: 600;
            width: 40%;
        }
        .val {
            color: #0f172a;
            font-family: monospace;
            font-weight: 600;
            text-align: right;
            width: 60%;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 20px 28px;
            text-align: center;
            font-size: 12px;
            line-height: 1.6;
            color: #64748b;
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
                            <p class="header-tagline">Mail System & Transactional Delivery Test</p>
                        </div>

                        <!-- Content -->
                        <div class="content-body">
                            <div class="status-badge">&#10003; Live Delivery Verified</div>
                            <h1 class="greeting">Mail System is Operational!</h1>
                            <p class="description">
                                This email confirms that the <strong>NEXVIA</strong> email delivery service is configured properly, authenticated with Gmail SMTP, and communicating successfully with external recipients.
                            </p>

                            <!-- Diagnostics Table -->
                            <table class="diag-table" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="label">Delivery Protocol</td>
                                    <td class="val">{{ strtoupper($mailer ?? 'SMTP') }} ({{ $encryption ?? 'TLS' }})</td>
                                </tr>
                                <tr>
                                    <td class="label">Mail Server Host</td>
                                    <td class="val">{{ $host ?? 'smtp.gmail.com' }}:{{ $port ?? 587 }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Sender Identity</td>
                                    <td class="val">{{ $fromAddress ?? 'nexviadls@gmail.com' }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Recipient Verified</td>
                                    <td class="val">{{ $recipient }}</td>
                                </tr>
                                <tr>
                                    <td class="label">Server Timestamp</td>
                                    <td class="val">{{ $timestamp }}</td>
                                </tr>
                            </table>

                            <p style="font-size: 13px; color: #475569; margin: 0 0 16px;">
                                Your application is ready to deliver OTP verification codes, customer booking receipts, and partner approvals securely.
                            </p>

                            <p style="font-size: 14px; color: #334155; margin: 0;">
                                Warm regards,<br>
                                <strong>NEXVIA Engineering Team</strong>
                            </p>
                        </div>

                        <!-- Footer -->
                        <div class="footer">
                            <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                                &copy; {{ date('Y') }} NEXVIA. Automated Infrastructure Verification.
                            </p>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
