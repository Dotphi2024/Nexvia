<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NEXVIA™ DSP Application Approved</title>
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
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
            padding: 36px 32px;
            text-align: center;
        }
        .header-logo {
            font-size: 28px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0 0 6px 0;
        }
        .header-tagline {
            color: #93c5fd;
            font-size: 13px;
            letter-spacing: 1px;
            margin: 0;
            text-transform: uppercase;
        }
        .status-badge-container {
            text-align: center;
            margin-top: -18px;
            margin-bottom: 24px;
        }
        .status-badge {
            display: inline-block;
            background-color: #10b981;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 8px 22px;
            border-radius: 50px;
            text-transform: uppercase;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.35);
        }
        .body-content {
            padding: 24px 36px 36px 36px;
        }
        .greeting {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 12px 0;
        }
        .lead-text {
            font-size: 15px;
            line-height: 1.6;
            color: #334155;
            margin: 0 0 24px 0;
        }
        .credentials-card {
            background-color: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 22px 24px;
            margin: 24px 0;
        }
        .credentials-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 16px 0;
            display: block;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
        }
        .credential-row {
            margin-bottom: 12px;
        }
        .credential-row:last-child {
            margin-bottom: 0;
        }
        .credential-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 3px;
            display: block;
        }
        .credential-value {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            font-family: 'Courier New', Courier, monospace;
            background: #ffffff;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            display: inline-block;
            word-break: break-all;
        }
        .details-table {
            width: 100%;
            margin: 20px 0;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }
        .details-table td {
            padding: 10px 14px;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
        }
        .details-table tr:last-child td {
            border-bottom: none;
        }
        .details-table .label {
            color: #64748b;
            font-weight: 600;
            width: 40%;
            background-color: #f8fafc;
        }
        .details-table .val {
            color: #0f172a;
            font-weight: 600;
        }
        .btn-container {
            text-align: center;
            margin: 32px 0 24px 0;
        }
        .btn-primary {
            display: inline-block;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff !important;
            text-decoration: none;
            font-size: 16px;
            font-weight: 700;
            padding: 14px 34px;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
            text-align: center;
        }
        .steps-box {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 16px 20px;
            border-radius: 0 8px 8px 0;
            margin: 24px 0;
        }
        .steps-title {
            font-size: 14px;
            font-weight: 700;
            color: #1e3a8a;
            margin: 0 0 8px 0;
        }
        .steps-list {
            margin: 0;
            padding-left: 20px;
            font-size: 13px;
            color: #1e40af;
            line-height: 1.6;
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 24px 32px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
        }
        .footer a {
            color: #2563eb;
            text-decoration: none;
        }
        .security-note {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 20px;
            font-style: italic;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main-container">
            <!-- Header -->
            <div class="header">
                <h1 class="header-logo">NEXVIA™</h1>
                <p class="header-tagline">Authorised Delivery & Service Partner Network</p>
            </div>

            <!-- Status Pill -->
            <div class="status-badge-container">
                <span class="status-badge">Application Approved</span>
            </div>

            <!-- Body -->
            <div class="body-content">
                <h2 class="greeting">Congratulations, {{ $dsp->applicant_name }}!</h2>
                <p class="lead-text">
                    We are pleased to inform you that your franchise application for <strong>{{ $dsp->business_name ?: $dsp->applicant_name }}</strong> has been officially <strong>approved</strong> by the NEXVIA management team.
                </p>

                <!-- Allotted Details Table -->
                <table class="details-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="label">Application Number</td>
                        <td class="val">{{ $dsp->application_number }}</td>
                    </tr>
                    <tr>
                        <td class="label">Business Name</td>
                        <td class="val">{{ $dsp->business_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Assigned Territory</td>
                        <td class="val">{{ $dsp->district ? $dsp->district . ', ' : '' }}{{ $dsp->state ?: 'Allotted Territory' }}</td>
                    </tr>
                    @if($dsp->pincodes)
                    <tr>
                        <td class="label">Service Pincodes</td>
                        <td class="val">{{ $dsp->pincodes }}</td>
                    </tr>
                    @endif
                </table>

                <!-- Credentials Card -->
                <div class="credentials-card">
                    <span class="credentials-title">Your Portal Login Credentials</span>

                    <div class="credential-row">
                        <span class="credential-label">Portal URL</span>
                        <div>
                            <a href="{{ $loginUrl }}" style="color: #2563eb; font-weight: 600; font-size: 14px; text-decoration: underline;">
                                {{ $loginUrl }}
                            </a>
                        </div>
                    </div>

                    <div class="credential-row" style="margin-top: 12px;">
                        <span class="credential-label">Registered Mobile (Username)</span>
                        <div class="credential-value">{{ $dsp->mobile }}</div>
                    </div>

                    <div class="credential-row" style="margin-top: 12px;">
                        <span class="credential-label">Password</span>
                        <div class="credential-value">{{ $password }}</div>
                    </div>
                </div>

                <!-- Call to Action Button -->
                <div class="btn-container">
                    <a href="{{ $loginUrl }}" class="btn-primary" target="_blank">
                        Access DSP Partner Portal &rarr;
                    </a>
                </div>

                <!-- Next Steps Box -->
                <div class="steps-box">
                    <div class="steps-title">Next Steps for Onboarding:</div>
                    <ol class="steps-list">
                        <li>Log in to the DSP Portal using your mobile number and password.</li>
                        <li>Update your banking / UPI details under the <strong>Profile & Payout Settings</strong> tab.</li>
                        <li>Review incoming customer bookings and local product delivery assignments.</li>
                        <li>For security, you may change your temporary password under Profile Settings after your first login.</li>
                    </ol>
                </div>

                <div class="security-note">
                    Please keep these credentials confidential. Never share your password with unauthorized individuals.
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p style="margin: 0 0 6px 0;">
                    This official notification was sent by <strong>NEXVIA Operations</strong> via <strong>{{ config('mail.from.address', 'nexviadls@gmail.com') }}</strong>.
                </p>
                <p style="margin: 0 0 6px 0;">
                    Need help getting started? Reach out to our partner desk at <a href="mailto:{{ config('mail.from.address', 'nexviadls@gmail.com') }}">{{ config('mail.from.address', 'nexviadls@gmail.com') }}</a>.
                </p>
                <p style="margin: 0; color: #94a3b8; font-size: 11px;">
                    &copy; {{ date('Y') }} NEXVIA™. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
