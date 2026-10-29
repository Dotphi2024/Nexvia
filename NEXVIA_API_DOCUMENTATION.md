# NEXVIA Mobility & Electronics — Complete API Reference Manual

> **Base URL:** `https://your-domain.com/api` (Local Dev: `http://127.0.0.1:8000/api`)  
> **API Version:** v1 / Direct REST  
> **Content-Type:** `application/json` / `multipart/form-data` (for file uploads)  
> **Authentication Scheme:** `Authorization: Bearer <token>` or `token: <token>` header or `user_id` parameter.  
> **DSP Partner Auth Scheme:** `Authorization: Bearer <dsp_token>` or `token: <token>` header or `dsp_id` parameter.

---

## Table of Contents

1. [Authentication & Customer Account APIs](#1-authentication--customer-account-apis)
   - [1.1 Register Customer](#11-register-customer)
   - [1.2 Login Customer (Passwordless / Phone)](#12-login-customer-passwordless--phone)
   - [1.3 Send OTP](#13-send-otp)
   - [1.4 Verify OTP](#14-verify-otp)
   - [1.5 Refresh Token](#15-refresh-token)
   - [1.6 Forgot Password OTP](#16-forgot-password-otp)
   - [1.7 Resend OTP](#17-resend-otp)
   - [1.8 Customer Logout](#18-customer-logout)
2. [Customer Profile & Delivery Address APIs](#2-customer-profile--delivery-address-apis)
   - [2.1 Get Customer Profile](#21-get-customer-profile)
   - [2.2 Update Profile & Avatar](#22-update-profile--avatar)
   - [2.3 Update FCM Device Token](#23-update-fcm-device-token)
   - [2.4 Get Saved Addresses](#24-get-saved-addresses)
   - [2.5 Add or Update Address](#25-add-or-update-address)
   - [2.6 Delete Address](#26-delete-address)
3. [Home Sliders & Promotional Banners APIs](#3-home-sliders--promotional-banners-apis)
   - [3.1 Get Home Banners](#31-get-home-banners)
   - [3.2 Get Side Banners](#32-get-side-banners)
   - [3.3 Record Banner Click / Tap](#33-record-banner-click--tap)
4. [Categories & Reward Progression Slabs APIs](#4-categories--reward-progression-slabs-apis)
   - [4.1 List All Categories](#41-list-all-categories)
   - [4.2 Single Category Detail & Products](#42-single-category-detail--products)
   - [4.3 Subcategories by Category](#43-subcategories-by-category)
5. [Products Catalog & Search APIs](#5-products-catalog--search-apis)
   - [5.1 Paginated Products Catalog](#51-paginated-products-catalog)
   - [5.2 Single Product Details & Specifications](#52-single-product-details--specifications)
   - [5.3 Product Search & Advanced Filtering](#53-product-search--advanced-filtering)
   - [5.4 Trending & Lightning Deals](#54-trending--lightning-deals)
6. [Wishlist & Favorites APIs](#6-wishlist--favorites-apis)
   - [6.1 Get Wishlist Items](#61-get-wishlist-items)
   - [6.2 Add Product to Wishlist](#62-add-product-to-wishlist)
   - [6.3 1-Click Toggle Wishlist Item](#63-1-click-toggle-wishlist-item)
   - [6.4 Check Product Status in Wishlist](#64-check-product-status-in-wishlist)
   - [6.5 Remove Item from Wishlist](#65-remove-item-from-wishlist)
   - [6.6 Clear Wishlist](#66-clear-wishlist)
7. [Shopping Cart Management APIs](#7-shopping-cart-management-apis)
   - [7.1 Get Cart Items & Financial Summary](#71-get-cart-items--financial-summary)
   - [7.2 Add Item to Cart](#72-add-item-to-cart)
   - [7.3 Update Cart Item Quantity / Color](#73-update-cart-item-quantity--color)
   - [7.4 Remove Item from Cart](#74-remove-item-from-cart)
   - [7.5 Clear Cart](#75-clear-cart)
8. [Checkout Calculation & Gateway Payment APIs](#8-checkout-calculation--gateway-payment-apis)
   - [8.1 Compute Checkout & Tax Breakdown](#81-compute-checkout--tax-breakdown)
   - [8.2 Create Gateway Payment Order (Razorpay)](#82-create-gateway-payment-order-razorpay)
   - [8.3 Verify Payment Callback & Signature](#83-verify-payment-callback--signature)
9. [Flexi-Bookings & 60-Day Balance Engine APIs](#9-flexi-bookings--60-day-balance-engine-apis)
   - [9.1 List User Bookings](#91-list-user-bookings)
   - [9.2 Create Confirmed 20% Flexi-Booking](#92-create-confirmed-20-flexi-booking)
   - [9.3 Get Booking Detail & Receipt](#93-get-booking-detail--receipt)
   - [9.4 Pay Remaining 80% Balance (Cash / Wallet)](#94-pay-remaining-80-balance-cash--wallet)
   - [9.5 Initiate Booking Transfer](#95-initiate-booking-transfer)
   - [9.6 Confirm Booking Transfer via OTP](#96-confirm-booking-transfer-via-otp)
   - [9.7 Select Preferred DSP Hub for Delivery](#97-select-preferred-dsp-hub-for-delivery)
   - [9.8 Reallocate Stored Paid Amount / Exchange Product](#98-reallocate-stored-paid-amount--exchange-product)
   - [9.9 Cancel Booking Policy Endpoint](#99-cancel-booking-policy-endpoint)
   - [9.10 Download / View Digital Delivery Challan (API)](#910-download--view-digital-delivery-challan-api)
10. [Multi-Item Orders & Delivery Tracking APIs](#10-multi-item-orders--delivery-tracking-apis)
    - [10.1 Multi-Item Order Checkout](#101-multi-item-order-checkout)
    - [10.2 Track 7-Stage Order Delivery](#102-track-7-stage-order-delivery)
11. [Warranty & Customer Service Support APIs](#11-warranty--customer-service-support-apis)
    - [11.1 List Customer Warranties](#111-list-customer-warranties)
    - [11.2 Create Service / Repair Ticket](#112-create-service--repair-ticket)
    - [11.3 List Customer Service Tickets](#113-list-customer-service-tickets)
    - [11.4 Get Service Ticket Details](#114-get-service-ticket-details)
    - [11.5 Lookup Local DSP Partners by Pincode / District](#115-lookup-local-dsp-partners-by-pincode--district)
    - [11.6 Schedule or Rate Technician Installation](#116-schedule-or-rate-technician-installation)
12. [Self Dealer Ecosystem & Referral Dashboard APIs](#12-self-dealer-ecosystem--referral-dashboard-apis)
    - [12.1 Customer Referral & Wallet Dashboard](#121-customer-referral--wallet-dashboard)
    - [12.2 Self Dealer Eligibility & Status (v1)](#122-self-dealer-eligibility--status-v1)
    - [12.3 Self Dealer Category 5-Stage Progression (v1)](#123-self-dealer-category-5-stage-progression-v1)
    - [12.4 Self Dealer Category Detail (v1)](#124-self-dealer-category-detail-v1)
    - [12.5 Self Dealer Incentive Points Wallet (v1)](#125-self-dealer-incentive-points-wallet-v1)
    - [12.6 Self Dealer Wallet Transaction Ledger (v1)](#126-self-dealer-wallet-transaction-ledger-v1)
    - [12.7 Self Dealer Referrals List (v1)](#127-self-dealer-referrals-list-v1)
    - [12.8 Apply & Validate Referral Code at Checkout (v1)](#128-apply--validate-referral-code-at-checkout-v1)
    - [12.9 Product Referral Benefit Calculator (v1)](#129-product-referral-benefit-calculator-v1)
    - [12.10 Redeem Incentive Points on Booking Balance (v1)](#1210-redeem-incentive-points-on-booking-balance-v1)
13. [Summary of Standard HTTP Status Codes](#13-summary-of-standard-http-status-codes)
14. [Razorpay Payment Gateway Integration (Live APIs)](#14-razorpay-payment-gateway-integration-live-apis)
    - [14.1 Live Gateway Credentials](#141-live-gateway-credentials)
    - [14.2 Create Gateway Payment Order](#142-create-gateway-payment-order)
    - [14.3 Verify Payment & Cryptographic Signature](#143-verify-payment--cryptographic-signature)
    - [14.4 Razorpay Webhook Event Listener](#144-razorpay-webhook-event-listener)
    - [14.5 Mobile & Web SDK Client Integration Code](#145-mobile--web-sdk-client-integration-code)
15. [Digital Delivery Challan (DC) Automatic Generation (100% Full Payment Engine)](#15-digital-delivery-challan-dc-automatic-generation-100-full-payment-engine)
    - [15.1 Challan Generation Rule & Architecture](#151-challan-generation-rule--architecture)
    - [15.2 Get Digital Delivery Challan Payload (API)](#152-get-digital-delivery-challan-payload-api)
    - [15.3 Digital Delivery Challan Web & Print Route](#153-digital-delivery-challan-web--print-route)
16. [Stored Paid Amount / Product Credit Reallocation & Purchasing Other Items](#16-stored-paid-amount--product-credit-reallocation--purchasing-other-items)
    - [16.1 Reallocate Paid Amount to Product Credit Wallet](#161-reallocate-paid-amount-to-product-credit-wallet)
    - [16.2 Book or Buy New Catalog Item Using Wallet Credits](#162-book-or-buy-new-catalog-item-using-wallet-credits)
17. [Authorised Delivery & Service Partner (DSP) Full Partner Ecosystem](#17-authorised-delivery--service-partner-dsp-full-partner-ecosystem)
    - [17.1 DSP Architecture & Commission Workflow Overview](#171-dsp-architecture--commission-workflow-overview)
    - [17.2 Submit DSP Partner Application](#172-submit-dsp-partner-application)
    - [17.3 Track Application Status](#173-track-application-status)
    - [17.4 DSP Partner Login](#174-dsp-partner-login)
    - [17.5 DSP Partner Logout](#175-dsp-partner-logout)
    - [17.6 Check Available DSPs by Pincode & Nearby Discovery](#176-check-available-dsps-by-pincode--nearby-discovery)
    - [17.7 DSP Dashboard Stats & Operations Overview](#177-dsp-dashboard-stats--operations-overview)
    - [17.8 Get DSP Profile & Payout Settings](#178-get-dsp-profile--payout-settings)
    - [17.9 Update DSP Profile & Payout Credentials](#179-update-dsp-profile--payout-credentials)
    - [17.10 List Assigned Territory Deliveries](#1710-list-assigned-territory-deliveries)
    - [17.11 Single Delivery Shipment Details](#1711-single-delivery-shipment-details)
    - [17.12 Product Received Confirmation at DSP Hub](#1712-product-received-confirmation-at-dsp-hub)
    - [17.13 Update Delivery Stage Status](#1713-update-delivery-stage-status)
    - [17.14 Customer Delivery OTP Verification & Automatic Warranty](#1714-customer-delivery-otp-verification--automatic-warranty)
    - [17.15 DSP Commission Wallet & Earnings Ledger](#1715-dsp-commission-wallet--earnings-ledger)
    - [17.16 Request Cash Payout / Bank or UPI Withdrawal](#1716-request-cash-payout--bank-or-upi-withdrawal)
    - [17.17 List Payout Requests History](#1717-list-payout-requests-history)
    - [17.18 List Assigned Service Requests](#1718-list-assigned-service-requests)
    - [17.19 Service Request Detail](#1719-service-request-detail)
    - [17.20 Update Service Request Attendance & Resolution Status](#1720-update-service-request-attendance--resolution-status)
18. [Subcategories & DLS Agro / Farm Equipment Catalog APIs](#18-subcategories--dls-agro--farm-equipment-catalog-apis)
    - [18.1 List All Subcategories](#181-list-all-subcategories)
    - [18.2 Subcategories by Category](#182-subcategories-by-category)
    - [18.3 Single Subcategory Details](#183-single-subcategory-details)
    - [18.4 DLS Agro Categories Catalog](#184-dls-agro-categories-catalog)
    - [18.5 DLS Agro Subcategories by Category](#185-dls-agro-subcategories-by-category)
    - [18.6 DLS Agro All Subcategories](#186-dls-agro-all-subcategories)
    - [18.7 DLS Agro Subcategory Detail](#187-dls-agro-subcategory-detail)
    - [18.8 DLS Agro Products Catalog with Specs & Filters](#188-dls-agro-products-catalog-with-specs--filters)
    - [18.9 DLS Agro Featured Agricultural Equipment](#189-dls-agro-featured-agricultural-equipment)
    - [18.10 DLS Agro Single Product Details](#1810-dls-agro-single-product-details)
    - [18.11 DLS Agro Hero & Section Banners](#1811-dls-agro-hero--section-banners)
    - [18.12 DLS Agro Side Banners](#1812-dls-agro-side-banners)
19. [Website Timed Popup Lead Capture APIs](#19-website-timed-popup-lead-capture-apis)
    - [19.1 Get Popup Configuration & Delay](#191-get-popup-configuration--delay)
    - [19.2 Submit Popup Lead Enquiry](#192-submit-popup-lead-enquiry)
    - [19.3 Frontend React / Vanilla JS Quick Integration Guide](#193-frontend-react--vanilla-js-quick-integration-guide)
20. [CMS Dynamic Pages & Legal Policy APIs](#20-cms-dynamic-pages--legal-policy-apis)
    - [20.1 Get Privacy Policy](#201-get-privacy-policy)
    - [20.2 Get Terms & Conditions](#202-get-terms--conditions)
    - [20.3 Get Refund & Cancellation Policy](#203-get-refund--cancellation-policy)
    - [20.4 Get About Us Page](#204-get-about-us-page)
    - [20.5 Get Contact Us Details](#205-get-contact-us-details)
    - [20.6 Submit Contact Us Inquiry](#206-submit-contact-us-inquiry)
    - [20.7 List All Dynamic CMS Pages](#207-list-all-dynamic-cms-pages)
    - [20.8 Get Single CMS Page by Slug or ID](#208-get-single-cms-page-by-slug-or-id)
21. [System Diagnostics, Automated Deployment & Utilities](#21-system-diagnostics-automated-deployment--utilities)
    - [21.1 API Root Health Check](#211-api-root-health-check)
    - [21.2 Automated Deployment Webhook](#212-automated-deployment-webhook)
    - [21.3 Live Mail Diagnostics & Test Email](#213-live-mail-diagnostics--test-email)

---

## 1. Authentication & Customer Account APIs

### 1.1 Register Customer
Create a new customer account and generate unique referral code and API token.

- **Method:** `POST`
- **URL:** `/api/auth/register` (Alias: `/api/customer/register`)
- **Auth Required:** No

#### Request Body
```json
{
  "fullName": "Rahul Sharma",
  "phone": "9876543210",
  "password": "Password@123",
  "password_confirmation": "Password@123",
  "email": "rahul.sharma@example.com",
  "referral_code": "NEX12345",
  "pincode": "411001",
  "city": "Pune",
  "state": "Maharashtra",
  "fcm_token": "fcm_device_token_here",
  "terms_accepted": true,
  "terms_version": "v1.0"
}
```

#### Field Specifications:
| Field | Type | Required | Description |
|---|---|---|---|
| `fullName` / `name` | string | **Yes** | Full customer name (min: 2 chars) |
| `phone` | string | **Yes** | 10-digit mobile number (`^[6-9]\d{9}$`) |
| `password` | string | **Yes** | Account password (min: 6 chars) |
| `password_confirmation` | string | No | Password confirmation |
| `email` | string | No | Unique email address |
| `referral_code` | string | No | Optional referral code of inviter |
| `pincode` | string | No | 6-digit postal code |
| `city` | string | No | City of residence |
| `state` | string | No | State of residence |
| `fcm_token` | string | No | Firebase Cloud Messaging device token |

#### Success Response (`201 Created`)
```json
{
  "status": true,
  "message": "Registration successful.",
  "token": "4f8a1e2d3c9b7a6f5e4d3c2b1a0f9e8d7c6b5a4f3e2d1c0b9a8f7e6d5c4b3a2",
  "data": {
    "id": 12,
    "fullName": "Rahul Sharma",
    "name": "Rahul Sharma",
    "phone": "9876543210",
    "email": "rahul.sharma@example.com",
    "referral_code": "NEX98B2A1",
    "referral_url": "https://your-domain.com/ref/NEX98B2A1",
    "is_self_dealer": false,
    "self_dealer_status": "inactive",
    "wallet_balance": 0.00,
    "city": "Pune",
    "state": "Maharashtra",
    "pincode": "411001",
    "created_at": "2026-10-16 10:15:30"
  }
}
```

#### Error Response (`422 Unprocessable Content`)
```json
{
  "status": false,
  "message": "The phone has already been taken.",
  "errors": {
    "phone": [
      "The phone has already been taken."
    ]
  }
}
```

---

### 1.2 Login Customer (Passwordless / Phone)
Authenticate customer using mobile number and password, or phone + OTP.

- **Method:** `POST`
- **URL:** `/api/auth/login` (Alias: `/api/customer/login`)
- **Auth Required:** No

#### Request Body
```json
{
  "phone": "9876543210",
  "password": "Password@123",
  "fcm_token": "fcm_token_optional"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Login successful.",
  "token": "4f8a1e2d3c9b7a6f5e4d3c2b1a0f9e8d7c6b5a4f3e2d1c0b9a8f7e6d5c4b3a2",
  "data": {
    "id": 12,
    "fullName": "Rahul Sharma",
    "phone": "9876543210",
    "email": "rahul.sharma@example.com",
    "referral_code": "NEX98B2A1",
    "is_self_dealer": true,
    "self_dealer_status": "active",
    "wallet_balance": 18500.00,
    "avatar": "https://backend.nexviadls.com/uploads/avatars/user_12.jpg"
  }
}
```

#### Error Response (`401 Unauthorized`)
```json
{
  "status": false,
  "message": "Invalid credentials. Please check your password or use OTP login."
}
```

---

### 1.3 Send OTP
Send a 6-digit one-time password to customer's mobile number via SMS gateway.

- **Method:** `POST`
- **URL:** `/api/auth/send-otp` (Alias: `/api/customer/send-otp`)
- **Auth Required:** No

#### Request Body
```json
{
  "phone": "9876543210",
  "purpose": "login"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "OTP has been sent successfully to +91 9876543210.",
  "expires_in_seconds": 300,
  "resend_in_seconds": 60
}
```

---

### 1.4 Verify OTP
Verify OTP and authenticate or complete action.

- **Method:** `POST`
- **URL:** `/api/auth/verify-otp` (Alias: `/api/customer/verify-otp`)
- **Auth Required:** No

#### Request Body
```json
{
  "phone": "9876543210",
  "otp": "492810"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "OTP verified successfully.",
  "token": "4f8a1e2d3c9b7a6f5e4d3c2b1a0f9e8d7c6b5a4f3e2d1c0b9a8f7e6d5c4b3a2",
  "user": {
    "id": 12,
    "name": "Rahul Sharma",
    "phone": "9876543210",
    "referral_code": "NEX98B2A1",
    "is_self_dealer": true
  }
}
```

#### Error Response (`422 Unprocessable Content`)
```json
{
  "status": false,
  "message": "Invalid or expired OTP. Please request a new one.",
  "error_code": "OTP_INVALID"
}
```

---

### 1.5 Refresh Token
Regenerate a new persistent API token.

- **Method:** `POST`
- **URL:** `/api/auth/refresh-token` (Alias: `/api/customer/refresh-token`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Token refreshed successfully.",
  "token": "7a9b1c2d3e4f5a6b7c8d9e0f1a2b3c4d5e6f7a8b9c0d1e2f3a4b5c6d7e8f9a0b"
}
```

---

### 1.6 Forgot Password OTP
Initiate password recovery by sending reset OTP.

- **Method:** `POST`
- **URL:** `/api/auth/forgot-password` (Alias: `/api/customer/forgot-password`)
- **Auth Required:** No

#### Request Body
```json
{
  "phone": "9876543210"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Password reset OTP sent to your registered mobile number."
}
```

---

### 1.7 Resend OTP
Resend OTP with rate-limiting check.

- **Method:** `POST`
- **URL:** `/api/auth/resend-otp` (Alias: `/api/customer/resend-otp`)
- **Auth Required:** No

#### Request Body
```json
{
  "phone": "9876543210"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "OTP has been resent successfully.",
  "expires_in_seconds": 300
}
```

---

### 1.8 Customer Logout
Invalidate current session and API token.

- **Method:** `POST` or `GET`
- **URL:** `/api/auth/logout` (Aliases: `/api/customer/logout`, `/api/logout`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Logged out successfully. Token invalidated."
}
```

---

## 2. Customer Profile & Delivery Address APIs

### 2.1 Get Customer Profile
Retrieve customer personal data, Self-Dealer status, and wallet balance.

- **Method:** `GET` or `POST`
- **URL:** `/api/user/profile` (Alias: `/api/customer/profile`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "id": 12,
    "name": "Rahul Sharma",
    "phone": "9876543210",
    "email": "rahul.sharma@example.com",
    "avatar": "https://backend.nexviadls.com/uploads/avatars/user_12.jpg",
    "referral_code": "NEX98B2A1",
    "referral_url": "https://your-domain.com/ref/NEX98B2A1",
    "is_self_dealer": true,
    "self_dealer_status": "active",
    "self_dealer_code": "SD-12-PUN",
    "wallet_balance": 18500.00,
    "address": "Flat 402, Sunshine Heights, FC Road",
    "city": "Pune",
    "state": "Maharashtra",
    "pincode": "411005"
  }
}
```

---

### 2.2 Update Profile & Avatar
Update customer profile fields and optionally upload profile photo.

- **Method:** `PUT` or `POST`
- **URL:** `/api/user/profile` (Aliases: `/api/user/update-profile`, `/api/customer/update-profile`)
- **Content-Type:** `multipart/form-data` or `application/json`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "name": "Rahul A. Sharma",
  "email": "rahul.new@example.com",
  "city": "Pune",
  "state": "Maharashtra",
  "pincode": "411005",
  "address": "Flat 402, Sunshine Heights, FC Road"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Profile updated successfully.",
  "data": {
    "id": 12,
    "name": "Rahul A. Sharma",
    "email": "rahul.new@example.com",
    "city": "Pune",
    "state": "Maharashtra",
    "pincode": "411005"
  }
}
```

---

### 2.3 Update FCM Device Token
Update push notification token for order tracking & OTP alerts.

- **Method:** `POST`
- **URL:** `/api/customer/update-fcm-token` (or within `/api/user/profile`)
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "fcm_token": "e5ZqX7...fcm_token_string..."
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "FCM device token updated successfully."
}
```

---

### 2.4 Get Saved Addresses
List customer delivery addresses.

- **Method:** `GET` or `POST`
- **URL:** `/api/user/addresses` (Aliases: `/api/customer/addresses`, `/api/addresses`, `/api/addresses/list`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": [
    {
      "id": 5,
      "address_type": "home",
      "recipient_name": "Rahul Sharma",
      "phone": "9876543210",
      "address_line1": "Flat 402, Sunshine Heights",
      "address_line2": "Off FC Road, Shivajinagar",
      "city": "Pune",
      "state": "Maharashtra",
      "pincode": "411005",
      "landmark": "Near Fergusson College Gate",
      "is_default": true
    }
  ]
}
```

---

### 2.5 Add or Update Address
Save a new delivery address or update existing.

- **Method:** `POST`
- **URL:** `/api/user/addresses` (Aliases: `/api/customer/addresses`, `/api/addresses`, `/api/addresses/add`)
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "address_type": "home",
  "recipient_name": "Rahul Sharma",
  "phone": "9876543210",
  "address_line1": "Flat 402, Sunshine Heights",
  "address_line2": "Off FC Road",
  "city": "Pune",
  "state": "Maharashtra",
  "pincode": "411005",
  "landmark": "Near FC College",
  "is_default": true
}
```

#### Success Response (`201 Created`)
```json
{
  "status": true,
  "message": "Delivery address saved successfully.",
  "data": {
    "id": 5,
    "recipient_name": "Rahul Sharma",
    "pincode": "411005",
    "is_default": true
  }
}
```

---

### 2.6 Delete Address
Remove a saved address.

- **Method:** `DELETE` or `POST`
- **URL:** `/api/user/addresses/{id}` (Aliases: `/api/user/addresses/delete`, `/api/addresses/delete`, `/api/customer/addresses/{id}`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Address removed successfully."
}
```

---

## 3. Home Sliders & Promotional Banners APIs

### 3.1 Get Home Banners
Fetch active hero sliders, category strips, and promotional banners for the main app / web homepage.

- **Method:** `GET`
- **URL:** `/api/home/banners` (Aliases: `/api/banners`, `/api/home-banners`, `/api/customer/banners`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Banners retrieved successfully.",
  "data": [
    {
      "id": 1,
      "title": "Revolutionary Electric Falcon",
      "subtitle": "Book at 20% Deposit • 60 Days Zero Interest Balance",
      "badge_text": "LIMITED TIME OFFER",
      "banner_type": "hero",
      "position": "home_main_slider",
      "target_type": "product",
      "target_id": "1",
      "link_url": "/products/nexvia-falcon-ev",
      "button_text": "Book at 20%",
      "image_url": "https://backend.nexviadls.com/uploads/banners/falcon_hero.webp",
      "sort_order": 1
    }
  ]
}
```

---

### 3.2 Get Side Banners
Retrieve active promotional side banners displayed in category grids and product detail sidebars.

- **Method:** `GET`
- **URL:** `/api/banners/side` (Alias: `/api/home/banners/side`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": [
    {
      "id": 4,
      "title": "5-Stage Self Dealer Rewards",
      "subtitle": "Earn up to 20% cash points on every referral",
      "banner_type": "side",
      "image_url": "https://backend.nexviadls.com/uploads/banners/side_promo.webp",
      "link_url": "/self-dealer-program"
    }
  ]
}
```

---

### 3.3 Record Banner Click / Tap
Increment click analytics when a customer clicks a banner.

- **Method:** `POST`
- **URL:** `/api/banners/{id}/click`
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Click recorded successfully.",
  "clicks_count": 142
}
```

---

## 4. Categories & Reward Progression Slabs APIs

### 4.1 List All Categories
Fetch all active top-level categories (Electric Mobility, Electronics & Appliances, DLS Farm Equipment).

- **Method:** `GET` or `POST`
- **URL:** `/api/categories` (Aliases: `/api/customer/categories`, `/api/auth/categories`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "count": 3,
  "data": [
    {
      "id": 1,
      "name": "Electric Vehicles & Scooters",
      "slug": "electric-vehicles",
      "type": "electric_mobility",
      "referral_category_code": "EV",
      "referral_eligible": true,
      "commission_percentage": 20.00,
      "description": "High-performance smart electric two-wheelers and scooters",
      "image": "https://backend.nexviadls.com/uploads/categories/ev_category.webp",
      "subcategories_count": 4,
      "products_count": 12
    },
    {
      "id": 2,
      "name": "Electronics & Smart Home",
      "slug": "electronics-appliances",
      "type": "electronics",
      "referral_category_code": "EL",
      "referral_eligible": true,
      "commission_percentage": 15.00,
      "description": "Smart LED TVs, washing machines, and premium cooling appliances",
      "image": "https://backend.nexviadls.com/uploads/categories/electronics.webp",
      "subcategories_count": 6,
      "products_count": 28
    }
  ]
}
```

---

### 4.2 Single Category Detail & Products
Get full category details with child subcategories and active catalog products.

- **Method:** `GET`
- **URL:** `/api/categories/{idOrSlug}` (Alias: `/api/customer/categories/{idOrSlug}`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "id": 1,
    "name": "Electric Vehicles & Scooters",
    "slug": "electric-vehicles",
    "type": "electric_mobility",
    "referral_category_code": "EV",
    "commission_percentage": 20.00,
    "subcategories": [
      {
        "id": 1,
        "name": "High-Speed Scooters",
        "slug": "high-speed-scooters",
        "products_count": 4
      }
    ],
    "products": [
      {
        "id": 1,
        "name": "NEXVIA Falcon EV",
        "slug": "nexvia-falcon-ev",
        "mrp": 125000.00,
        "booking_amount": 25000.00,
        "balance_amount": 100000.00,
        "booking_percentage": 20.00
      }
    ]
  }
}
```

---

### 4.3 Subcategories by Category
List all subcategories belonging to a parent category.

- **Method:** `GET` or `POST`
- **URL:** `/api/categories/{idOrSlug}/subcategories`
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "category": {
    "id": 1,
    "name": "Electric Vehicles & Scooters",
    "slug": "electric-vehicles"
  },
  "subcategories": [
    {
      "id": 1,
      "name": "High-Speed Scooters",
      "slug": "high-speed-scooters",
      "image": "https://backend.nexviadls.com/uploads/subcategories/subcat_1.webp",
      "products_count": 4
    }
  ]
}
```

---

## 5. Products Catalog & Search APIs

### 5.1 Paginated Products Catalog
Fetch product catalog with category, subcategory, price, and sorting filters.

- **Method:** `GET` or `POST`
- **URL:** `/api/products` (Aliases: `/api/customer/products`, `/api/auth/products`)
- **Auth Required:** No

#### Query Parameters:
| Parameter | Type | Description |
|---|---|---|
| `category_id` / `cat_id` | int/slug | Filter by category ID or slug |
| `subcategory_id` / `subcat_id` | int/slug | Filter by subcategory |
| `search` | string | Keyword search |
| `sort_by` | string | `price_low`, `price_high`, `newest`, `popular` |
| `page` | int | Page number (default: 1) |
| `limit` / `per_page` | int | Items per page (default: 12) |

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "total": 42,
  "current_page": 1,
  "last_page": 4,
  "per_page": 12,
  "products": [
    {
      "id": 1,
      "name": "NEXVIA Falcon EV",
      "slug": "nexvia-falcon-ev",
      "model_code": "NX-FLC-2026",
      "sku": "NX-EV-FLC-01",
      "mrp": 125000.00,
      "booking_percentage": 20.00,
      "booking_amount": 25000.00,
      "balance_amount": 100000.00,
      "balance_duration_days": 60,
      "self_dealer_eligible": true,
      "referral_eligible": true,
      "main_image": "https://backend.nexviadls.com/uploads/products/falcon_main.webp",
      "stock": 25,
      "in_stock": true,
      "category": {
        "id": 1,
        "name": "Electric Vehicles",
        "slug": "electric-vehicles"
      }
    }
  ]
}
```

---

### 5.2 Single Product Details & Specifications
Fetch full specifications, color variants, gallery images, and 20% booking deposit calculator.

- **Method:** `GET` or `POST`
- **URL:** `/api/products/{idOrSlug}` (Aliases: `/api/products/detail/{idOrSlug}`, `/api/customer/products/{idOrSlug}`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "id": 1,
    "name": "NEXVIA Falcon EV Smart Electric Scooter",
    "slug": "nexvia-falcon-ev",
    "model_code": "NX-FLC-2026",
    "sku": "NX-EV-FLC-01",
    "mrp": 125000.00,
    "booking_percentage": 20.00,
    "booking_amount": 25000.00,
    "balance_amount": 100000.00,
    "balance_due_days": 60,
    "eligible_referral_value": 125000.00,
    "self_dealer_eligible": true,
    "referral_eligible": true,
    "main_image": "https://backend.nexviadls.com/uploads/products/falcon_main.webp",
    "gallery": [
      "https://backend.nexviadls.com/uploads/products/falcon_angle1.webp",
      "https://backend.nexviadls.com/uploads/products/falcon_angle2.webp"
    ],
    "colors": ["Matte Black", "Pearl White", "Crimson Red"],
    "specifications": [
      { "name": "Top Speed", "value": "85 km/h" },
      { "name": "Certified Range", "value": "145 km per charge" },
      { "name": "Battery Pack", "value": "3.8 kWh Lithium-Ion (IP67)" },
      { "name": "Charging Time", "value": "3.5 Hours (Fast Charge)" },
      { "name": "Official Warranty", "value": "3 Years Comprehensive" }
    ],
    "features": [
      "Keyless Start & Anti-Theft Alarm",
      "7-inch TFT Navigation Console",
      "Regenerative Braking System"
    ],
    "stock": 25
  }
}
```

---

### 5.3 Product Search & Advanced Filtering
Instant autocomplete and filtered catalog search.

- **Method:** `GET` or `POST`
- **URL:** `/api/products/search` (Alias: `/api/customer/products/search`)
- **Auth Required:** No

#### Request Query / Body:
```json
{
  "query": "Falcon",
  "min_price": 50000,
  "max_price": 150000
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "query": "Falcon",
  "count": 1,
  "products": [
    {
      "id": 1,
      "name": "NEXVIA Falcon EV",
      "slug": "nexvia-falcon-ev",
      "mrp": 125000.00,
      "booking_amount": 25000.00
    }
  ]
}
```

---

### 5.4 Trending & Lightning Deals
Fetch hot-selling and trending products.

- **Method:** `GET` or `POST`
- **URL:** `/api/products/trending` (Alias: `/api/customer/products/trending`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "count": 4,
  "products": [
    {
      "id": 1,
      "name": "NEXVIA Falcon EV",
      "slug": "nexvia-falcon-ev",
      "mrp": 125000.00,
      "tag": "TRENDING"
    }
  ]
}
```

---

## 6. Wishlist & Favorites APIs

### 6.1 Get Wishlist Items
List customer saved favorites with product pricing and stock status.

- **Method:** `GET` or `POST`
- **URL:** `/api/wishlist` (Aliases: `/api/wishlist/list`, `/api/customer/wishlist`, `/api/customer/wishlist/list`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "count": 1,
  "data": [
    {
      "id": 14,
      "product_id": 1,
      "product": {
        "id": 1,
        "name": "NEXVIA Falcon EV",
        "slug": "nexvia-falcon-ev",
        "mrp": 125000.00,
        "booking_amount": 25000.00,
        "image": "https://backend.nexviadls.com/uploads/products/falcon_main.webp"
      },
      "added_at": "2026-10-16 11:20:00"
    }
  ]
}
```

---

### 6.2 Add Product to Wishlist
Add item to favorites.

- **Method:** `POST`
- **URL:** `/api/wishlist` (Aliases: `/api/wishlist/add`, `/api/customer/wishlist`, `/api/customer/wishlist/add`)
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "product_id": 1
}
```

#### Success Response (`201 Created`)
```json
{
  "status": true,
  "message": "Product added to wishlist.",
  "is_in_wishlist": true
}
```

---

### 6.3 1-Click Toggle Wishlist Item
Toggle product in or out of wishlist in a single request.

- **Method:** `POST`
- **URL:** `/api/wishlist/toggle` (Alias: `/api/customer/wishlist/toggle`)
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "product_id": 1
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Wishlist updated.",
  "action": "added",
  "is_in_wishlist": true
}
```

---

### 6.4 Check Product Status in Wishlist
Check whether a specific product is in customer wishlist.

- **Method:** `GET`
- **URL:** `/api/wishlist/check/{productId}` (Alias: `/api/customer/wishlist/check/{productId}`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "product_id": 1,
  "is_in_wishlist": true
}
```

---

### 6.5 Remove Item from Wishlist
Remove item from wishlist.

- **Method:** `DELETE` or `POST`
- **URL:** `/api/wishlist/{productId?}` (Aliases: `/api/wishlist/remove`, `/api/wishlist/delete`, `/api/customer/wishlist/remove`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Product removed from wishlist.",
  "is_in_wishlist": false
}
```

---

### 6.6 Clear Wishlist
Remove all items from wishlist.

- **Method:** `DELETE` or `POST`
- **URL:** `/api/wishlist/clear` (Alias: `/api/customer/wishlist/clear`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Wishlist cleared successfully."
}
```

---

## 7. Shopping Cart Management APIs

### 7.1 Get Cart Items & Financial Summary
Retrieve active cart items with subtotal, tax breakdown, and total token deposit payable.

- **Method:** `GET` or `POST`
- **URL:** `/api/cart` (Aliases: `/api/cart/list`, `/api/customer/cart`, `/api/customer/cart/list`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "items": [
      {
        "id": 8,
        "product_id": 1,
        "name": "NEXVIA Falcon EV",
        "selected_color": "Matte Black",
        "quantity": 1,
        "unit_mrp": 125000.00,
        "unit_booking_amount": 25000.00,
        "total_mrp": 125000.00,
        "total_booking_deposit": 25000.00,
        "image": "https://backend.nexviadls.com/uploads/products/falcon_main.webp"
      }
    ],
    "summary": {
      "items_count": 1,
      "total_mrp": 125000.00,
      "total_booking_deposit": 25000.00,
      "total_balance_amount": 100000.00,
      "balance_due_days": 60
    }
  }
}
```

---

### 7.2 Add Item to Cart
Add product to customer cart.

- **Method:** `POST`
- **URL:** `/api/cart` (Aliases: `/api/cart/add`, `/api/customer/cart`, `/api/customer/cart/add`)
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "product_id": 1,
  "quantity": 1,
  "color": "Matte Black"
}
```

#### Success Response (`201 Created`)
```json
{
  "status": true,
  "message": "Item added to cart successfully.",
  "cart_items_count": 1
}
```

---

### 7.3 Update Cart Item Quantity / Color
Modify quantity or color of a cart item.

- **Method:** `POST` or `PUT`
- **URL:** `/api/cart/update` (Alias: `/api/customer/cart/update`)
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "cart_id": 8,
  "quantity": 2,
  "color": "Pearl White"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Cart item updated successfully."
}
```

---

### 7.4 Remove Item from Cart
Delete a single item from cart.

- **Method:** `DELETE` or `POST`
- **URL:** `/api/cart/{id}` (Aliases: `/api/cart/remove`, `/api/cart/delete`, `/api/customer/cart/remove`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Item removed from cart."
}
```

---

### 7.5 Clear Cart
Empty customer cart.

- **Method:** `DELETE` or `POST`
- **URL:** `/api/cart/clear` (Alias: `/api/customer/cart/clear`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Cart cleared successfully."
}
```

---

## 8. Checkout Calculation & Gateway Payment APIs

### 8.1 Compute Checkout & Tax Breakdown
Calculate net payable amount applying 20% token deposit rule, referral discounts, wallet credits, and delivery charges.

- **Method:** `POST`
- **URL:** `/api/checkout/calculate` (Alias: `/api/customer/checkout/calculate`)
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "product_id": 1,
  "quantity": 1,
  "use_wallet_points": true,
  "points_to_use": 5000,
  "referral_code": "NEX98B2A1",
  "pincode": "411005"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "product_mrp": 125000.00,
    "quantity": 1,
    "total_mrp": 125000.00,
    "token_deposit_percentage": 20.00,
    "required_token_deposit": 25000.00,
    "wallet_credits_applied": 5000.00,
    "net_cash_payable_now": 20000.00,
    "remaining_balance": 100000.00,
    "balance_due_days": 60,
    "balance_due_date": "2026-12-15",
    "delivery_charge": 0.00,
    "gst_included": true
  }
}
```

---

### 8.2 Create Gateway Payment Order (Razorpay)
Initialize live Razorpay Order ID for online checkout.

- **Method:** `POST`
- **URL:** `/api/payments/create-order` (Alias: `/api/customer/payments/create-order`)
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "amount": 20000.00,
  "currency": "INR",
  "booking_id": 18,
  "notes": {
    "purpose": "20_percent_token_deposit",
    "booking_number": "NEX-2026-98124"
  }
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Payment order generated successfully.",
  "data": {
    "order_id": "order_RNEX98B2A1772",
    "amount": 2000000,
    "currency": "INR",
    "key_id": "rzp_live_xxxxxxxxxxxxxx",
    "name": "NEXVIA Mobility",
    "description": "20% Flexi-Booking Token Deposit",
    "customer": {
      "name": "Rahul Sharma",
      "phone": "9876543210",
      "email": "rahul.sharma@example.com"
    }
  }
}
```

---

### 8.3 Verify Payment Callback & Signature
Verify HMAC SHA-256 cryptographic signature after payment gateway completion.

- **Method:** `POST`
- **URL:** `/api/payments/verify` (Alias: `/api/customer/payments/verify`)
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "razorpay_order_id": "order_RNEX98B2A1772",
  "razorpay_payment_id": "pay_RNEX98B2A1889",
  "razorpay_signature": "9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c4d3e2f1a0b",
  "booking_id": 18
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Payment verified and booking confirmed successfully!",
  "booking": {
    "id": 18,
    "booking_number": "NEX-2026-98124",
    "payment_status": "partially_paid",
    "booking_status": "confirmed",
    "paid_amount": 25000.00,
    "balance_amount": 100000.00,
    "balance_due_date": "2026-12-15"
  }
}
```

---

## 9. Flexi-Bookings & 60-Day Balance Engine APIs

### 9.1 List User Bookings
Fetch all customer flexi-bookings with countdown to 60-day balance settlement.

- **Method:** `GET` or `POST`
- **URL:** `/api/bookings` (Aliases: `/api/bookings/list`, `/api/customer/bookings`, `/api/customer/bookings/list`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "total": 1,
  "bookings": [
    {
      "id": 18,
      "booking_number": "NEX-2026-98124",
      "product_name": "NEXVIA Falcon EV",
      "model_code": "NX-FLC-2026",
      "selected_color": "Matte Black",
      "mrp": 125000.00,
      "paid_amount": 25000.00,
      "balance_amount": 100000.00,
      "payment_status": "partially_paid",
      "booking_status": "confirmed",
      "booking_date": "2026-10-16",
      "balance_due_date": "2026-12-15",
      "days_remaining": 60,
      "can_pay_balance": true,
      "can_transfer": true,
      "challan_available": false
    }
  ]
}
```

---

### 9.2 Create Confirmed 20% Flexi-Booking
Place a 20% deposit flexi-booking for a catalog vehicle, electronic appliance, or farm equipment.

- **Method:** `POST`
- **URL:** `/api/bookings` (Aliases: `/api/booking`, `/api/customer/bookings`)
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "product_id": 1,
  "selected_color": "Matte Black",
  "quantity": 1,
  "shipping_address": "Flat 402, Sunshine Heights, FC Road",
  "city": "Pune",
  "state": "Maharashtra",
  "pincode": "411005",
  "use_wallet_points": false,
  "referral_code": "NEX98B2A1",
  "payment_method": "razorpay"
}
```

#### Success Response (`201 Created`)
```json
{
  "status": true,
  "message": "Flexi-Booking created successfully. 60-day balance countdown initiated.",
  "booking": {
    "id": 18,
    "booking_number": "NEX-2026-98124",
    "product_name": "NEXVIA Falcon EV",
    "mrp": 125000.00,
    "deposit_paid": 25000.00,
    "balance_due": 100000.00,
    "balance_due_date": "2026-12-15",
    "booking_status": "confirmed",
    "self_dealer_activated": true
  }
}
```

---

### 9.3 Get Booking Detail & Receipt
Get detailed receipt, DSP assignment, payment ledger, and warranty timeline.

- **Method:** `GET`
- **URL:** `/api/bookings/{id}` (Alias: `/api/customer/bookings/{id}`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "id": 18,
    "booking_number": "NEX-2026-98124",
    "product_name": "NEXVIA Falcon EV",
    "mrp": 125000.00,
    "paid_amount": 25000.00,
    "balance_amount": 100000.00,
    "payment_status": "partially_paid",
    "booking_status": "confirmed",
    "assigned_dsp": {
      "id": 2,
      "business_name": "Nexvia Hub Shivajinagar",
      "mobile": "9822001122",
      "district": "Pune"
    },
    "delivery": {
      "tracking_number": "TRK-20261016-8910",
      "stage": "order_confirmed"
    }
  }
}
```

---

### 9.4 Pay Remaining 80% Balance (Cash / Wallet)
Settle the remaining 80% balance using payment gateway or available incentive points wallet.

- **Method:** `POST`
- **URL:** `/api/customer/bookings/{id}/pay-balance`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "amount": 100000.00,
  "payment_method": "razorpay",
  "use_wallet_points": true,
  "points_to_redeem": 10000.00
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Balance paid in full! 100% Payment complete. Digital Delivery Challan generated.",
  "booking": {
    "booking_number": "NEX-2026-98124",
    "payment_status": "fully_paid",
    "booking_status": "balance_paid",
    "balance_amount": 0.00,
    "challan_number": "DC-2026-98124",
    "challan_url": "https://your-domain.com/api/bookings/18/challan"
  }
}
```

---

### 9.5 Initiate Booking Transfer
Initiate transfer of a confirmed booking and its paid deposit to another person's mobile number.

- **Method:** `POST`
- **URL:** `/api/customer/bookings/{id}/transfer`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "recipient_phone": "9823456789",
  "recipient_name": "Amit Deshmukh"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Transfer OTP sent to your registered mobile number. Please confirm OTP to complete transfer.",
  "transfer_id": "TRF-98124-77"
}
```

---

### 9.6 Confirm Booking Transfer via OTP
Confirm booking transfer with OTP.

- **Method:** `POST`
- **URL:** `/api/customer/bookings/{id}/transfer/confirm`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "otp": "719302",
  "transfer_id": "TRF-98124-77"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Booking transferred successfully to Amit Deshmukh (9823456789).",
  "booking_number": "NEX-2026-98124"
}
```

---

### 9.7 Select Preferred DSP Hub for Delivery
Assign or change the Authorised Delivery & Service Partner (DSP) hub for local delivery and PDI handover.

- **Method:** `POST`
- **URL:** `/api/customer/bookings/{id}/select-dsp` (Aliases: `/api/bookings/{id}/select-dsp`, `/api/booking/{id}/select-dsp`)
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "dsp_id": 2
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "DSP Partner 'Nexvia Hub Shivajinagar' assigned to booking #NEX-2026-98124. Delivery and unboxing will be coordinated by this partner.",
  "booking_number": "NEX-2026-98124",
  "assigned_dsp": {
    "id": 2,
    "business_name": "Nexvia Hub Shivajinagar",
    "mobile": "9822001122",
    "territory": "Shivajinagar & FC Road",
    "address": "Showroom #12, Ground Floor, Pune Center",
    "pincode": "411005",
    "district": "Pune",
    "state": "Maharashtra"
  }
}
```

---

### 9.8 Reallocate Stored Paid Amount / Exchange Product
Reallocate total paid amounts (20% token deposit + partial payments) either to a new product booking or to the customer's Product Credit Wallet.

- **Method:** `POST`
- **URL:** `/api/customer/bookings/{id}/reallocate` (Aliases: `/api/bookings/{id}/reallocate`, `/api/customer/bookings/{id}/exchange-product`)
- **Headers:** `Authorization: Bearer <token>`

#### Request Body (Direct Product Switch)
```json
{
  "new_product_id": 5
}
```

#### Request Body (Reallocate to Wallet)
```json
{
  "reallocate_to_wallet": true
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "₹25,000.00 successfully transferred to your Product Credit Wallet! You can use these credits to book or purchase any product in the catalog.",
  "credited_amount": 25000.00,
  "wallet_balance": 25000.00
}
```

---

### 9.9 Cancel Booking Policy Endpoint
Endpoint enforcing the strict non-cancellation terms for 20% token deposits.

- **Method:** `POST`
- **URL:** `/api/customer/bookings/{id}/cancel` (Alias: `/api/bookings/{id}/cancel`)
- **Headers:** `Authorization: Bearer <token>`

#### Response (`422 Unprocessable Content`)
```json
{
  "status": false,
  "message": "Cancellation is not permitted. Once the 20% booking deposit is confirmed, bookings are strictly non-cancellable and non-refundable. You may instead reallocate your paid amount to purchase any other catalog item."
}
```

---

### 9.10 Download / View Digital Delivery Challan (API)
Retrieve structured data of the Digital Delivery Challan (DC) generated automatically upon 100% full balance payment.

- **Method:** `GET` or `POST`
- **URL:** `/api/bookings/{id}/challan` (Aliases: `/api/booking/{id}/challan`, `/api/customer/bookings/{id}/challan`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "challan": {
    "challan_number": "DC-2026-98124",
    "issued_date": "2026-10-16 12:45:00",
    "booking_number": "NEX-2026-98124",
    "customer": {
      "name": "Rahul Sharma",
      "phone": "9876543210",
      "address": "Flat 402, Sunshine Heights, FC Road, Pune 411005"
    },
    "product": {
      "name": "NEXVIA Falcon EV",
      "model_code": "NX-FLC-2026",
      "selected_color": "Matte Black",
      "serial_number": "NX-EV-2026-CH77102"
    },
    "payment_summary": {
      "total_mrp": 125000.00,
      "total_paid": 125000.00,
      "balance_due": 0.00,
      "payment_status": "fully_paid"
    },
    "assigned_dsp": {
      "business_name": "Nexvia Hub Shivajinagar",
      "phone": "9822001122"
    }
  }
}
```

---

## 10. Multi-Item Orders & Delivery Tracking APIs

### 10.1 Multi-Item Order Checkout
Place multi-item cart order for accessories, electronics, or parts.

- **Method:** `POST`
- **URL:** `/api/customer/orders/checkout`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "shipping_address_id": 5,
  "payment_method": "razorpay",
  "notes": "Deliver during business hours"
}
```

#### Success Response (`201 Created`)
```json
{
  "status": true,
  "message": "Order placed successfully.",
  "order": {
    "id": 9,
    "order_number": "ORD-2026-5541",
    "total_amount": 14999.00,
    "tracking_number": "TRK-ORD-5541",
    "payment_status": "paid"
  }
}
```

---

### 10.2 Track 7-Stage Order Delivery
Track delivery status through the official 7-stage pipeline.

- **Method:** `GET`
- **URL:** `/api/deliveries/{trackingNumber}` (Alias: `/api/customer/deliveries/{trackingNumber}`)
- **Auth Required:** No

#### 7-Stage Delivery Pipeline:
1. `order_confirmed`
2. `assigned` (Assigned to DSP Hub)
3. `packed`
4. `dispatched` (In Transit from Warehouse)
5. `received_at_dsp` (Physically verified at DSP Center)
6. `out_for_delivery`
7. `delivered` (Verified with Customer OTP)

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "delivery": {
    "tracking_number": "TRK-46384600",
    "current_stage": "out_for_delivery",
    "stage_index": 6,
    "total_stages": 7,
    "product_name": "NEXVIA Falcon EV",
    "customer_name": "Rahul Sharma",
    "shipping_city": "Pune",
    "shipping_pincode": "411005",
    "assigned_dsp": {
      "business_name": "Nexvia Hub Shivajinagar",
      "contact_phone": "9822001122"
    },
    "timeline": [
      { "stage": "order_confirmed", "time": "2026-10-14 10:00:00", "completed": true },
      { "stage": "assigned", "time": "2026-10-14 14:00:00", "completed": true },
      { "stage": "packed", "time": "2026-10-15 09:30:00", "completed": true },
      { "stage": "dispatched", "time": "2026-10-15 16:00:00", "completed": true },
      { "stage": "received_at_dsp", "time": "2026-10-16 09:00:00", "completed": true },
      { "stage": "out_for_delivery", "time": "2026-10-16 11:30:00", "completed": true },
      { "stage": "delivered", "time": null, "completed": false }
    ]
  }
}
```

---

## 11. Warranty & Customer Service Support APIs

### 11.1 List Customer Warranties
List all automated 3-year digital warranties activated upon OTP-verified delivery.

- **Method:** `GET`
- **URL:** `/api/customer/warranties`
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "product_name": "NEXVIA Falcon EV",
      "model_code": "NX-FLC-2026",
      "serial_number": "NX-EV-2026-CH77102",
      "purchase_date": "2026-10-16",
      "warranty_start": "2026-10-16",
      "warranty_end": "2029-10-16",
      "status": "active",
      "document_url": "https://backend.nexviadls.com/uploads/warranties/w_1.pdf",
      "action": "RAISE SERVICE REQUEST"
    }
  ]
}
```

---

### 11.2 Create Service / Repair Ticket
Customer files a warranty complaint or repair request. The system automatically allocates the nearest regional Authorised DSP partner based on pincode.

- **Method:** `POST`
- **URL:** `/api/customer/service-tickets` (Alias: `/api/customer/service-requests`)
- **Content-Type:** `multipart/form-data` or `application/json`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body:
| Field | Type | Required | Description |
|---|---|---|---|
| `subject` | string | **Yes** | Issue summary (e.g., "Brake calibration required") |
| `service_type` | string | **Yes** | `warranty`, `installation`, `repair`, `technical_support`, `maintenance`, `breakdown` |
| `details` | string | **Yes** | Detailed description of problem |
| `priority` | string | No | `low`, `medium`, `high`, `urgent` (default: `medium`) |
| `booking_id` | integer | No | Linked booking ID |
| `pincode` | string | No | Customer service location pincode |
| `photo` | file | No | JPEG/PNG/WebP image of issue (max: 10MB) |
| `video` | file | No | MP4/MOV clip showing fault (max: 50MB) |

#### Success Response (`201 Created`)
```json
{
  "status": true,
  "success": true,
  "message": "Service request submitted successfully. Allocated to Authorised DSP 'Nexvia Hub Shivajinagar' for PIN 411005.",
  "data": {
    "id": 4,
    "ticket_number": "TKT-2026-88192",
    "subject": "Brake calibration required",
    "service_type": "warranty",
    "priority": "high",
    "status": "open",
    "is_attended": false,
    "location": {
      "address": "Flat 402, Sunshine Heights, FC Road",
      "pincode": "411005",
      "city": "Pune",
      "state": "Maharashtra"
    },
    "allocated_dsp": {
      "id": 2,
      "business_name": "Nexvia Hub Shivajinagar",
      "contact_name": "Suresh Patil",
      "mobile": "9822001122",
      "district": "Pune",
      "state": "Maharashtra"
    },
    "created_at": "2026-10-16 14:10:00"
  }
}
```

---

### 11.3 List Customer Service Tickets
List all service tickets raised by customer with technician attended status.

- **Method:** `GET`
- **URL:** `/api/customer/service-tickets` (Alias: `/api/customer/service-requests`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": [
    {
      "id": 4,
      "ticket_number": "TKT-2026-88192",
      "subject": "Brake calibration required",
      "service_type": "warranty",
      "status": "attended",
      "is_attended": true,
      "attended_at": "2026-10-16 15:00:00",
      "attended_by": "Vikram Singh (Technician)",
      "allocated_dsp": {
        "id": 2,
        "business_name": "Nexvia Hub Shivajinagar",
        "mobile": "9822001122"
      }
    }
  ]
}
```

---

### 11.4 Get Service Ticket Details
Fetch single ticket details, notes, resolution proof, and DSP details.

- **Method:** `GET`
- **URL:** `/api/customer/service-tickets/{id}` (Alias: `/api/customer/service-requests/{id}`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "id": 4,
    "ticket_number": "TKT-2026-88192",
    "subject": "Brake calibration required",
    "service_type": "warranty",
    "priority": "high",
    "status": "resolved",
    "is_attended": true,
    "attended_at": "2026-10-16 15:00:00",
    "attended_by": "Vikram Singh",
    "attended_by_phone": "9822001122",
    "details": "Front disc brake lever loose",
    "dsp_notes": "Technician visited customer premises, bled brake line and tightened caliper",
    "resolved_at": "2026-10-16 16:30:00",
    "resolution_notes": "Issue resolved and customer signed service completion slip.",
    "allocated_dsp": {
      "id": 2,
      "business_name": "Nexvia Hub Shivajinagar",
      "mobile": "9822001122"
    }
  }
}
```

---

### 11.5 Lookup Local DSP Partners by Pincode / District
Find authorised local DSP centers for on-site inspection or servicing.

- **Method:** `GET` or `POST`
- **URL:** `/api/customer/dsp/lookup`
- **Headers:** `Authorization: Bearer <token>`

#### Request Query / Body:
```json
{
  "pincode": "411005",
  "city": "Pune",
  "state": "Maharashtra"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "count": 2,
  "data": [
    {
      "id": 2,
      "business_name": "Nexvia Hub Shivajinagar",
      "contact_name": "Suresh Patil",
      "mobile": "9822001122",
      "district": "Pune",
      "state": "Maharashtra",
      "match_type": "exact_pincode",
      "match_label": "Authorised Delivery & Service Center"
    }
  ]
}
```

---

### 11.6 Schedule or Rate Technician Installation
Schedule doorstep unboxing, battery setup, or submit technician rating.

- **Method:** `POST`
- **URL:** `/api/customer/installations/schedule`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "booking_id": 18,
  "scheduled_at": "2026-10-18 11:00:00",
  "rating": 5,
  "feedback": "Prompt and polite technician. Explained smart console clearly."
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Installation schedule updated.",
  "data": {
    "id": 1,
    "technician_name": "Assigned Technician",
    "technician_phone": "+91-9876543210",
    "scheduled_at": "2026-10-18 11:00:00",
    "status": "completed",
    "rating": 5
  }
}
```

---

## 12. Self Dealer Ecosystem & Referral Dashboard APIs

### 12.1 Customer Referral & Wallet Dashboard
Combined overview of referral link, QR code, 5-stage category progressions, referred customers, and ledger.

- **Method:** `GET`
- **URL:** `/api/customer/referral-dashboard`
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "is_self_dealer": true,
    "self_dealer_code": "SD-12-PUN",
    "referral_code": "NEX98B2A1",
    "referral_url": "https://your-domain.com/ref/NEX98B2A1",
    "qr_code_data": "NEXVIA_REF:NEX98B2A1",
    "wallet": {
      "available_credit": 4500.00,
      "pending_credit": 1000.00,
      "used_credit": 15000.00,
      "lifetime_credit": 20500.00,
      "redeem_label": "REDEEM FOR PRODUCT",
      "can_withdraw_cash": false
    },
    "category_progress": [
      {
        "category_id": 1,
        "category_name": "Electric Vehicles",
        "category_slug": "electric-vehicles",
        "category_code": "EV",
        "current_stage": 2,
        "max_stages": 5,
        "cycle_number": 1,
        "successful_referrals": 1,
        "total_referrals_all_time": 1,
        "current_benefit_pct": 12.00,
        "next_benefit_pct": 15.00,
        "progress_status": "Stage 2/5 (12%) • Cycle #1"
      }
    ],
    "referred_customers": [
      {
        "id": 18,
        "name": "Kavita Shah",
        "phone_masked": "98******10",
        "joined_at": "2026-03-05",
        "booking_status": "Booked",
        "credit_earned": 8999.90,
        "benefit_pct": 10.00
      }
    ],
    "wallet_ledger": [
      {
        "id": 42,
        "amount": 8999.90,
        "type": "credit",
        "source": "referral_booking",
        "status": "available",
        "stage": 1,
        "percentage": 10.00,
        "description": "Referral commission (10%) for booking NEX-2026-781923 by Kavita Shah",
        "date": "2026-03-05 14:00:00"
      }
    ]
  }
}
```

---

### 12.2 Self Dealer Eligibility & Status (v1)
Check if customer is an active Self-Dealer and view activation terms.

- **Method:** `GET`
- **URL:** `/api/v1/self-dealer/status` (Alias: `/api/customer/self-dealer/status`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "is_self_dealer": true,
    "self_dealer_status": "active",
    "activated_at": "2026-10-16 10:15:30",
    "referral_code": "NEX98B2A1",
    "referral_url": "https://your-domain.com/ref/NEX98B2A1",
    "can_earn_referral_points": true
  }
}
```

---

### 12.3 Self Dealer Category 5-Stage Progression (v1)
View 5-stage progression slabs across all categories.

- **Method:** `GET`
- **URL:** `/api/v1/self-dealer/categories` (Alias: `/api/customer/self-dealer/categories`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": [
    {
      "category_id": 1,
      "category_name": "Electric Vehicles",
      "category_code": "EV",
      "current_stage": 1,
      "max_stages": 5,
      "current_rate_pct": 10.00,
      "next_rate_pct": 12.00,
      "stage_rates": [10.00, 12.00, 15.00, 18.00, 20.00],
      "cycle_number": 1
    }
  ]
}
```

---

### 12.4 Self Dealer Category Detail (v1)
Detail of single category progression.

- **Method:** `GET`
- **URL:** `/api/v1/self-dealer/category/{id}` (Alias: `/api/customer/self-dealer/category/{id}`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "category_id": 1,
    "category_name": "Electric Vehicles",
    "current_stage": 1,
    "cycle_number": 1,
    "stage_rates": [10.0, 12.0, 15.0, 18.0, 20.0]
  }
}
```

---

### 12.5 Self Dealer Incentive Points Wallet (v1)
Get incentive points wallet balance and point conversion rate.

- **Method:** `GET` or `POST`
- **URL:** `/api/v1/self-dealer/wallet` (Aliases: `/api/customer/self-dealer/wallet`, `/api/customer/wallet`, `/api/wallet`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "available_points": 18500.00,
    "pending_points": 0.00,
    "redeemed_points": 5000.00,
    "lifetime_points": 23500.00,
    "point_value_inr": 1.00
  }
}
```

---

### 12.6 Self Dealer Wallet Transaction Ledger (v1)
Paginated history of points credited and redeemed.

- **Method:** `GET` or `POST`
- **URL:** `/api/v1/self-dealer/wallet/transactions` (Aliases: `/api/customer/self-dealer/wallet/transactions`, `/api/customer/transactions`, `/api/wallet/transactions`, `/api/transactions`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "amount": 12500.00,
      "type": "credit",
      "source": "referral_booking",
      "description": "Stage 1 referral points for booking NEX-2026-9810",
      "created_at": "2026-10-16 11:00:00"
    }
  ]
}
```

---

### 12.7 Self Dealer Referrals List (v1)
List of invited customers and their booking statuses.

- **Method:** `GET` or `POST`
- **URL:** `/api/v1/self-dealer/referrals` (Aliases: `/api/customer/referrals`, `/api/customer/my-referrals`, `/api/referrals`, `/api/my-referrals`)
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": [
    {
      "id": 2,
      "name": "Kavita Shah",
      "phone": "98******10",
      "joined_date": "2026-10-15",
      "status": "booked",
      "points_earned": 12500.00
    }
  ]
}
```

---

### 12.8 Apply & Validate Referral Code at Checkout (v1)
Validate a referral code and apply inviter benefits.

- **Method:** `POST`
- **URL:** `/api/v1/referral/apply` (Alias: `/api/customer/referral/apply`)
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "referral_code": "NEX98B2A1"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Referral code applied successfully.",
  "data": {
    "referral_code": "NEX98B2A1",
    "referrer_name": "Rahul Sharma",
    "applied": true
  }
}
```

---

### 12.9 Product Referral Benefit Calculator (v1)
Calculate potential incentive points across all 5 stages for any product.

- **Method:** `GET`
- **URL:** `/api/v1/products/{id}/referral-benefit`
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "product_id": 1,
    "product_name": "NEXVIA Falcon EV",
    "mrp": 125000.00,
    "eligible_referral_value": 125000.00,
    "is_self_dealer_activator": true,
    "referral_eligible": true,
    "self_dealer_activation": {
      "activation_percentage": 20.00,
      "potential_points": 25000.00,
      "banner_text": "Book this product to activate Self Dealership and get ₹25,000.00 (20%) in points!"
    },
    "referral_stages": [
      { "stage": 1, "percentage": 10.00, "potential_points": 12500.00 },
      { "stage": 2, "percentage": 12.00, "potential_points": 15000.00 },
      { "stage": 3, "percentage": 15.00, "potential_points": 18750.00 },
      { "stage": 4, "percentage": 18.00, "potential_points": 22500.00 },
      { "stage": 5, "percentage": 20.00, "potential_points": 25000.00 }
    ]
  }
}
```

---

### 12.10 Redeem Incentive Points on Booking Balance (v1)
Redeem available points to pay down the remaining balance of an active booking.

- **Method:** `POST`
- **URL:** `/api/v1/self-dealer/redeem` (Alias: `/api/customer/self-dealer/redeem`)
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "booking_id": 18,
  "points": 5000
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Successfully redeemed 5000 points toward your booking.",
  "data": {
    "points_redeemed": 5000.00,
    "remaining_balance_due": 95000.00,
    "booking_payment_status": "partially_paid",
    "remaining_wallet_points": 13500.00,
    "self_dealer_cancelled": false,
    "self_dealer_status": "active",
    "is_self_dealer": true
  }
}
```

---

## 13. Summary of Standard HTTP Status Codes

| Code | Status | Meaning & Scenario |
|---|---|---|
| `200` | **OK** | Request completed successfully (read, update, delete). |
| `201` | **Created** | Resource created (register, create booking, service ticket). |
| `400` | **Bad Request** | Malformed JSON or invalid parameter syntax. |
| `401` | **Unauthorized** | Missing or invalid API bearer token. |
| `403` | **Forbidden** | Valid token but forbidden resource (e.g. another user's booking). |
| `404` | **Not Found** | Resource or endpoint does not exist. |
| `422` | **Unprocessable Content** | Validation error (e.g. invalid phone, duplicate, OTP mismatch). |
| `500` | **Server Error** | Unhandled server exception. |

---

## 14. Razorpay Payment Gateway Integration (Live APIs)

### 14.1 Live Gateway Credentials
- **Environment:** Live Production / Test Sandbox
- **Currency:** `INR` (all amounts in paise for SDK: `₹1 = 100 paise`)
- **Key ID:** `rzp_live_xxxxxxxxxxxxxx` (from `.env` config)

### 14.2 Create Gateway Payment Order
Generates server-side cryptographic order ID `order_xxxxxxxxxxxxxx`.

- **Method:** `POST`
- **URL:** `/api/payments/create-order`
- **Headers:** `Authorization: Bearer <token>`

### 14.3 Verify Payment & Cryptographic Signature
Validates signature: `hash_hmac('sha256', order_id + "|" + payment_id, secret)`.

- **Method:** `POST`
- **URL:** `/api/payments/verify`

### 14.4 Razorpay Webhook Event Listener
Listens to server-to-server gateway webhooks (`payment.captured`, `order.paid`).

- **Method:** `POST`
- **URL:** `/api/payments/webhook` (Alias: `/api/webhooks/razorpay`)
- **Headers:** `X-Razorpay-Signature: <signature>`

#### Webhook Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Webhook processed successfully"
}
```

### 14.5 Mobile & Web SDK Client Integration Code
Use standard Razorpay Checkout JS / Android / iOS SDK passing the returned `order_id` and `amount`.

---

## 15. Digital Delivery Challan (DC) Automatic Generation (100% Full Payment Engine)

### 15.1 Challan Generation Rule & Architecture
1. **Rule:** A booking generates a Digital Delivery Challan (DC) only when paid in full (`payment_status: 'fully_paid'`).
2. **Components Included:** Customer information, product details, serial/chassis number, tax breakdown, and DSP center handover authorization.

### 15.2 Get Digital Delivery Challan Payload (API)
- **Method:** `GET` or `POST`
- **URL:** `/api/bookings/{id}/challan`
- **Headers:** `Authorization: Bearer <token>`

### 15.3 Digital Delivery Challan Web & Print Route
Printable web view available at `https://your-domain.com/bookings/{id}/challan` for DSP printout and vehicle registration handover.

---

## 16. Stored Paid Amount / Product Credit Reallocation & Purchasing Other Items

### 16.1 Reallocate Paid Amount to Product Credit Wallet
If a customer chooses to switch items or 60 days pass without paying the balance, their paid money is never lost. It is credited to their Product Credit Wallet.

- **Method:** `POST`
- **URL:** `/api/customer/bookings/{id}/reallocate`

### 16.2 Book or Buy New Catalog Item Using Wallet Credits
Use `use_wallet_points: true` in `/api/bookings` or `/api/customer/orders/checkout` to purchase any catalog product with zero cash outflow.

---

## 17. Authorised Delivery & Service Partner (DSP) Full Partner Ecosystem

The Authorised Delivery & Service Partner (DSP) ecosystem empowers local hub operators, showrooms, and service centers to handle regional warehousing, Pre-Delivery Inspection (PDI), doorstep handover, OTP delivery confirmation, and post-sale warranty servicing.

### 17.1 DSP Architecture & Commission Workflow Overview
```text
[Central Factory / Warehouse]
          │ (Dispatch)
          ▼
[DSP Regional Hub] ──────► Action: "Product Received at DSP" (POST /api/dsp/deliveries/{id}/receive)
          │
          ▼
[Out for Delivery]  ─────► Action: Marked Out for Delivery (POST /api/dsp/deliveries/{id}/status)
          │
          ▼
[Doorstep Customer Handover]
          │
          ├──► DSP requests 6-digit Customer OTP
          ▼
[OTP Verification]  ─────► POST /api/dsp/deliveries/{id}/verify-otp
          │
          ├──► ❌ OTP Mismatch: Blocked (422 Error - Handover prevented)
          │
          └──► ✅ OTP Verified:
                 1. Order marked as DELIVERED
                 2. 5% DSP Commission automatically credited to DSP Wallet
                 3. Official 3-Year Customer Warranty activated automatically
```

---

### 17.2 Submit DSP Partner Application
Apply to become an Authorised Delivery & Service Partner (DSP) with territory selection, infrastructure specifications, and payment proof.

- **Method:** `POST`
- **URL:** `/api/dsp/apply`
- **Content-Type:** `multipart/form-data`
- **Auth Required:** No (Public Partner Application)

#### Request Fields:
| Field | Type | Required | Description |
|---|---|---|---|
| `applicant_name` | string | **Yes** | Full name of applicant |
| `mobile` | string | **Yes** | Primary 10-digit mobile number |
| `business_name` | string | **Yes** | Registered trade or business name |
| `preferred_territory_area` | string | **Yes** | Preferred territory / city / tehsil |
| `district` | string | **Yes** | District |
| `state` | string | **Yes** | State |
| `pincodes` | string | **Yes** | Serviced pincodes (comma-separated or array) |
| `declaration_agreed` | boolean | **Yes** | Must be `true` |
| `deposit_payment_proof` | file | No | Bank receipt for security deposit (PDF/JPG, max: 4MB) |
| `signature_file` | file | No | Applicant signature image (max: 4MB) |

#### Success Response (`201 Created`)
```json
{
  "status": true,
  "message": "DSP Partner application submitted successfully!",
  "application_number": "DSP-2026-X9K2L1",
  "data": {
    "application_number": "DSP-2026-X9K2L1",
    "applicant_name": "Suresh Patil",
    "business_name": "Patil Auto & Mobility Services",
    "mobile": "9822001122",
    "preferred_territory_area": "Shivajinagar & FC Road",
    "district": "Pune",
    "state": "Maharashtra",
    "status": "pending",
    "security_deposit_amount": 1000000.00,
    "created_at": "2026-10-16 10:00:00"
  }
}
```

---

### 17.3 Track Application Status
Track the review and approval status of a submitted DSP partner application.

- **Method:** `GET`
- **URL:** `/api/dsp/track/{applicationNumber}`
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "application_number": "DSP-2026-X9K2L1",
  "applicant_name": "Suresh Patil",
  "business_name": "Patil Auto & Mobility Services",
  "status": "approved",
  "applied_at": "2026-10-16T10:00:00+05:30"
}
```

---

### 17.4 DSP Partner Login
Authenticate DSP Partner and receive API session token.

- **Method:** `POST`
- **URL:** `/api/dsp/login`
- **Auth Required:** No

#### Request Body
```json
{
  "mobile": "9822001122",
  "password": "Password@123"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "DSP Partner authenticated successfully.",
  "token": "d8e7f6a5b4c3d2e1f0a9b8c7d6e5f4a3b2c1d0e9f8a7b6c5d4e3f2a1b0c9d8e7",
  "dsp": {
    "id": 2,
    "application_number": "DSP-2026-X9K2L1",
    "business_name": "Patil Auto & Mobility Services",
    "applicant_name": "Suresh Patil",
    "mobile": "9822001122",
    "email": "patil.auto@example.com",
    "territory_area": "Shivajinagar & FC Road",
    "serviced_pincodes": ["411005", "411004", "411016"],
    "district": "Pune",
    "state": "Maharashtra",
    "status": "approved",
    "wallet_balance": 37500.00,
    "total_earned": 50000.00,
    "total_redeemed": 12500.00,
    "commission_rate": "5.0%"
  }
}
```

---

### 17.5 DSP Partner Logout
Invalidate DSP session and bearer token.

- **Method:** `POST`
- **URL:** `/api/dsp/logout`
- **Headers:** `Authorization: Bearer <dsp_token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Logged out successfully."
}
```

---

### 17.6 Check Available DSPs by Pincode & Nearby Discovery
Discover authorised DSP partners assigned to a customer pincode or address.

- **Method:** `GET` or `POST`
- **URL:** `/api/dsp/available-by-pincode` (Alias: `/api/dsp/nearby`)
- **Auth Required:** No (Public Endpoint)

#### Query / Body Parameters:
```json
{
  "pincode": "411005",
  "city": "Pune",
  "state": "Maharashtra"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "search_criteria": {
    "pincode": "411005",
    "district": "Pune",
    "state": "Maharashtra",
    "detection_source": "query_parameters"
  },
  "count": 1,
  "has_direct_pincode_partner": true,
  "recommended_dsp": {
    "id": 2,
    "business_name": "Patil Auto & Mobility Services",
    "mobile": "9822001122",
    "territory_area": "Shivajinagar & FC Road",
    "premises_address": "Shop 4, Ground Floor, Shivajinagar",
    "premises_pincode": "411005",
    "serviced_pincodes": ["411005", "411004", "411016"],
    "match_type": "exact_pincode",
    "match_label": "Direct Pincode Partner",
    "is_direct_match": true,
    "commission_rate": "5.0%"
  },
  "dsps": [
    {
      "id": 2,
      "business_name": "Patil Auto & Mobility Services",
      "mobile": "9822001122",
      "pincode": "411005"
    }
  ]
}
```

---

### 17.7 DSP Dashboard Stats & Operations Overview
Comprehensive dashboard metrics for deliveries, wallet balance, and recent activities.

- **Method:** `GET` or `POST`
- **URL:** `/api/dsp/dashboard`
- **Headers:** `Authorization: Bearer <dsp_token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "dsp": {
      "id": 2,
      "business_name": "Patil Auto & Mobility Services",
      "mobile": "9822001122",
      "district": "Pune",
      "state": "Maharashtra",
      "serviced_pincodes": ["411005", "411004"]
    },
    "stats": {
      "total_deliveries": 24,
      "pending_deliveries": 3,
      "out_for_delivery": 2,
      "delivered": 19,
      "commission_rate": "5.0%"
    },
    "wallet": {
      "available_balance": 37500.00,
      "total_earned": 50000.00,
      "total_redeemed": 12500.00
    },
    "recent_deliveries": [
      {
        "id": 14,
        "tracking_number": "TRK-46384600",
        "order_number": "NEX-2026-98124",
        "product_name": "NEXVIA Falcon EV",
        "customer_name": "Rahul Sharma",
        "customer_phone": "9876543210",
        "stage": "out_for_delivery",
        "product_mrp": 125000.00,
        "dsp_commission": 6250.00,
        "commission_status": "pending"
      }
    ],
    "recent_transactions": [
      {
        "id": 102,
        "amount": 6250.00,
        "type": "credit",
        "source": "delivery_commission",
        "description": "5% Commission for delivery TRK-46384599",
        "balance_after": 37500.00,
        "created_at": "2026-10-15T16:20:00+05:30"
      }
    ]
  }
}
```

---

### 17.8 Get DSP Profile & Payout Settings
Retrieve partner contact info, premises address, and banking credentials.

- **Method:** `GET`
- **URL:** `/api/dsp/profile`
- **Headers:** `Authorization: Bearer <dsp_token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "dsp": {
    "id": 2,
    "application_number": "DSP-2026-X9K2L1",
    "business_name": "Patil Auto & Mobility Services",
    "applicant_name": "Suresh Patil",
    "mobile": "9822001122",
    "email": "patil.auto@example.com",
    "district": "Pune",
    "state": "Maharashtra",
    "payout_settings": {
      "account_number": "918237461928",
      "ifsc": "HDFC0001234",
      "bank_name": "HDFC Bank",
      "holder_name": "Patil Auto & Mobility Services",
      "upi_id": "patilauto@hdfcbank"
    },
    "wallet": {
      "balance": 37500.00,
      "total_earned": 50000.00,
      "total_redeemed": 12500.00
    }
  }
}
```

---

### 17.9 Update DSP Profile & Payout Credentials
Update bank account details, UPI ID, or login password.

- **Method:** `POST`
- **URL:** `/api/dsp/profile`
- **Headers:** `Authorization: Bearer <dsp_token>`

#### Request Body
```json
{
  "payout_account_number": "918237461928",
  "payout_ifsc": "HDFC0001234",
  "payout_bank_name": "HDFC Bank",
  "payout_holder_name": "Patil Auto & Mobility Services",
  "payout_upi_id": "patilauto@hdfcbank",
  "new_password": "NewSecurePassword@123"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "DSP Profile and payout settings updated successfully.",
  "dsp": {
    "id": 2,
    "payout_settings": {
      "account_number": "918237461928",
      "ifsc": "HDFC0001234",
      "bank_name": "HDFC Bank",
      "holder_name": "Patil Auto & Mobility Services",
      "upi_id": "patilauto@hdfcbank"
    }
  }
}
```

---

### 17.10 List Assigned Territory Deliveries
Fetch shipments assigned to DSP with stage filters (`pending`, `out_for_delivery`, `delivered`).

- **Method:** `GET` or `POST`
- **URL:** `/api/dsp/deliveries`
- **Headers:** `Authorization: Bearer <dsp_token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "current_page": 1,
  "total": 5,
  "per_page": 15,
  "last_page": 1,
  "deliveries": [
    {
      "id": 14,
      "tracking_number": "TRK-46384600",
      "order_number": "NEX-2026-98124",
      "stage": "received_at_dsp",
      "product": {
        "name": "NEXVIA Falcon EV",
        "model_code": "NX-FLC-2026",
        "selected_color": "Matte Black",
        "mrp": 125000.00
      },
      "customer": {
        "name": "Rahul Sharma",
        "phone": "9876543210"
      },
      "shipping_address": {
        "address": "Flat 402, Sunshine Heights, FC Road",
        "city": "Pune",
        "pincode": "411005"
      },
      "commission": {
        "rate": "5.0%",
        "amount": 6250.00,
        "status": "pending"
      }
    }
  ]
}
```

---

### 17.11 Single Delivery Shipment Details
Get complete details of a specific delivery order.

- **Method:** `GET` or `POST`
- **URL:** `/api/dsp/deliveries/{id}`
- **Headers:** `Authorization: Bearer <dsp_token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "delivery": {
    "id": 14,
    "tracking_number": "TRK-46384600",
    "order_number": "NEX-2026-98124",
    "stage": "received_at_dsp",
    "product": {
      "name": "NEXVIA Falcon EV",
      "model_code": "NX-FLC-2026",
      "selected_color": "Matte Black",
      "mrp": 125000.00
    },
    "customer": {
      "name": "Rahul Sharma",
      "phone": "9876543210",
      "email": "rahul.sharma@example.com"
    },
    "shipping_address": {
      "address": "Flat 402, Sunshine Heights, FC Road",
      "city": "Pune",
      "state": "Maharashtra",
      "pincode": "411005"
    },
    "commission": {
      "rate": "5.0%",
      "amount": 6250.00,
      "status": "pending"
    }
  }
}
```

---

### 17.12 Product Received Confirmation at DSP Hub
DSP confirms that physical vehicle or parcel arrived from central warehouse intact and passed initial inspection.

- **Method:** `POST`
- **URL:** `/api/dsp/deliveries/{id}/receive`
- **Headers:** `Authorization: Bearer <dsp_token>`

#### Request Body
```json
{
  "delivery_notes": "Unit received intact, battery sealed, verified model NEX-FALCON"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Product physical shipment received and verified at DSP Hub successfully. Ready for PDI and Out for Delivery.",
  "stage": "received_at_dsp",
  "received_at_dsp_at": "2026-10-16T11:23:32+05:30",
  "tracking_number": "TRK-46384600",
  "pdi_status": "received_at_hub"
}
```

---

### 17.13 Update Delivery Stage Status
Transition shipment stage to `out_for_delivery` or initiate delivery verification.

- **Method:** `POST`
- **URL:** `/api/dsp/deliveries/{id}/status`
- **Headers:** `Authorization: Bearer <dsp_token>`

#### Request Body
```json
{
  "stage": "out_for_delivery",
  "delivery_notes": "Loaded on transport van. Technician en route to customer location."
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Delivery marked as OUT FOR DELIVERY.",
  "stage": "out_for_delivery"
}
```

---

### 17.14 Customer Delivery OTP Verification & Automatic Warranty
Strict Security Handover Check: Order is marked delivered only after the customer provides the 6-digit OTP received via SMS. Verifying OTP automatically credits the 5% commission to DSP wallet and creates the 3-year warranty certificate.

- **Method:** `POST`
- **URL:** `/api/dsp/deliveries/{id}/verify-otp`
- **Headers:** `Authorization: Bearer <dsp_token>`

#### Request Body
```json
{
  "delivery_otp": "849201",
  "delivery_notes": "PDI passed, customer verified bike and keys handed over."
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Customer Delivery OTP verified successfully! Order marked as DELIVERED, 5% commission credited, and official warranty activated.",
  "stage": "delivered",
  "delivered_at": "2026-10-16T12:30:00+05:30",
  "commission_credited": 6250.00,
  "wallet_balance": 43750.00,
  "transaction_ref": "DSP-COM-20261016-7736",
  "warranty": {
    "id": 1,
    "serial_number": "NX-EV-2026-CH77102",
    "warranty_start": "2026-10-16",
    "warranty_end": "2029-10-16",
    "status": "active",
    "coverage_years": 3
  }
}
```

#### Error Response — Invalid OTP (`422 Unprocessable Content`)
```json
{
  "status": false,
  "message": "Invalid Customer Delivery OTP. Please enter the correct 6-digit OTP provided by the customer.",
  "error": "OTP_MISMATCH"
}
```

---

### 17.15 DSP Commission Wallet & Earnings Ledger
View DSP earnings balance, commissions credited from completed deliveries, and past payouts.

- **Method:** `GET` or `POST`
- **URL:** `/api/dsp/wallet`
- **Headers:** `Authorization: Bearer <dsp_token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "wallet": {
    "wallet_balance": 43750.00,
    "total_earned": 56250.00,
    "total_redeemed": 12500.00
  },
  "current_page": 1,
  "total": 8,
  "transactions": [
    {
      "id": 105,
      "amount": 6250.00,
      "type": "credit",
      "source": "delivery_commission",
      "reference_number": "DSP-COM-20261016-7736",
      "description": "5% Commission for delivery TRK-46384600",
      "balance_after": 43750.00,
      "created_at": "2026-10-16T12:30:00+05:30"
    }
  ]
}
```

---

### 17.16 Request Cash Payout / Bank or UPI Withdrawal
Request wallet balance redemption to bank account (NEFT/IMPS) or UPI ID. Minimum withdrawal is ₹100.

- **Method:** `POST`
- **URL:** `/api/dsp/wallet/redeem`
- **Headers:** `Authorization: Bearer <dsp_token>`

#### Request Body (Bank Payout)
```json
{
  "amount": 20000.00,
  "payout_mode": "bank",
  "bank_account_number": "918237461928",
  "bank_ifsc": "HDFC0001234",
  "bank_name": "HDFC Bank",
  "bank_holder_name": "Patil Auto & Mobility Services"
}
```

#### Request Body (UPI Payout)
```json
{
  "amount": 10000.00,
  "payout_mode": "upi",
  "upi_id": "patilauto@hdfcbank"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Payout request of ₹20,000.00 submitted successfully. Amount will be credited to your bank account within 24 business hours.",
  "payout_request": {
    "id": 8,
    "request_number": "DSP-PAY-20261016-01",
    "amount": 20000.00,
    "payout_mode": "bank",
    "status": "pending",
    "remaining_wallet_balance": 23750.00
  }
}
```

---

### 17.17 List Payout Requests History
History of partner redemption requests and bank settlement statuses.

- **Method:** `GET` or `POST`
- **URL:** `/api/dsp/wallet/payout-requests`
- **Headers:** `Authorization: Bearer <dsp_token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "total": 2,
  "data": [
    {
      "id": 8,
      "request_number": "DSP-PAY-20261016-01",
      "amount": 20000.00,
      "payout_mode": "bank",
      "status": "processed",
      "transaction_reference": "UTR9918274610",
      "requested_at": "2026-10-16 13:00:00",
      "processed_at": "2026-10-16 15:30:00"
    }
  ]
}
```

---

### 17.18 List Assigned Service Requests
List service or repair tickets assigned to this DSP partner.

- **Method:** `GET` or `POST`
- **URL:** `/api/dsp/service-requests`
- **Headers:** `Authorization: Bearer <dsp_token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "stats": {
    "total_assigned": 6,
    "attended_count": 4,
    "pending_attention": 2,
    "resolved_count": 3
  },
  "data": [
    {
      "id": 4,
      "ticket_number": "TKT-2026-88192",
      "customer_name": "Rahul Sharma",
      "customer_phone": "9876543210",
      "city": "Pune",
      "pincode": "411005",
      "subject": "Brake calibration required",
      "service_type": "warranty",
      "priority": "high",
      "status": "open",
      "is_attended": false,
      "created_at": "2026-10-16 14:10:00"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "total": 6
  }
}
```

---

### 17.19 Service Request Detail
Detailed diagnostic information, photos, videos, and customer address for a service ticket.

- **Method:** `GET` or `POST`
- **URL:** `/api/dsp/service-requests/{id}`
- **Headers:** `Authorization: Bearer <dsp_token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "id": 4,
    "ticket_number": "TKT-2026-88192",
    "customer_name": "Rahul Sharma",
    "customer_phone": "9876543210",
    "address": "Flat 402, Sunshine Heights, FC Road",
    "city": "Pune",
    "pincode": "411005",
    "subject": "Brake calibration required",
    "service_type": "warranty",
    "priority": "high",
    "status": "open",
    "is_attended": false,
    "details": "Front disc brake lever loose"
  }
}
```

---

### 17.20 Update Service Request Attendance & Resolution Status
DSP marks problem as attended, assigns visiting technician, inputs service notes, and marks resolved.

- **Method:** `POST`
- **URL:** `/api/dsp/service-requests/{id}/status`
- **Headers:** `Authorization: Bearer <dsp_token>`

#### Request Body
```json
{
  "status": "resolved",
  "is_attended": true,
  "attended_by_name": "Vikram Singh",
  "attended_by_phone": "9822001122",
  "dsp_notes": "Brake line bled and calibrated at customer doorstep.",
  "resolution_notes": "Testing complete. Brake responsiveness verified."
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Service ticket status updated successfully.",
  "ticket": {
    "id": 4,
    "ticket_number": "TKT-2026-88192",
    "status": "resolved",
    "is_attended": true,
    "attended_at": "2026-10-16 15:00:00",
    "resolved_at": "2026-10-16 16:30:00"
  }
}
```

---

## 18. Subcategories & DLS Agro / Farm Equipment Catalog APIs

### 18.1 List All Subcategories
Fetch all active subcategories across mobility, electronics, and agriculture.

- **Method:** `GET` or `POST`
- **URL:** `/api/subcategories` (Alias: `/api/customer/subcategories`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "total": 12,
  "subcategories": [
    {
      "id": 1,
      "name": "High-Speed Scooters",
      "slug": "high-speed-scooters",
      "image": "https://backend.nexviadls.com/uploads/subcategories/subcat_1.webp",
      "sort_order": 1,
      "products_count": 4,
      "category": {
        "id": 1,
        "name": "Electric Vehicles",
        "slug": "electric-vehicles",
        "type": "electric_mobility"
      }
    }
  ]
}
```

---

### 18.2 Subcategories by Category
Filter subcategories under a specific parent category.

- **Method:** `GET` or `POST`
- **URL:** `/api/categories/{idOrSlug}/subcategories`
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "subcategories": [
    {
      "id": 1,
      "name": "High-Speed Scooters",
      "slug": "high-speed-scooters",
      "products_count": 4
    }
  ]
}
```

---

### 18.3 Single Subcategory Details
Get single subcategory information and list of active products.

- **Method:** `GET` or `POST`
- **URL:** `/api/subcategories/{idOrSlug}` (Alias: `/api/customer/subcategories/{idOrSlug}`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "subcategory": {
    "id": 1,
    "name": "High-Speed Scooters",
    "slug": "high-speed-scooters",
    "description": "High performance smart electric scooters",
    "products_count": 4,
    "products": [
      {
        "id": 1,
        "name": "NEXVIA Falcon EV",
        "slug": "nexvia-falcon-ev",
        "mrp": 125000.00,
        "booking_amount": 25000.00
      }
    ]
  }
}
```

---

### 18.4 DLS Agro Categories Catalog
Fetch all categories dedicated to the DLS Agro & Farm Equipment division.

- **Method:** `GET` or `POST`
- **URL:** `/api/dls-agro/categories` (Alias: `/api/dls-farm/categories`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "DLS Agro categories retrieved successfully.",
  "count": 3,
  "data": [
    {
      "id": 3,
      "name": "Power Tillers & Weeders",
      "slug": "power-tillers-weeders",
      "type": "dls_farm_equipment",
      "referral_category_code": "AGRO",
      "referral_eligible": true,
      "commission_percentage": 10.00,
      "description": "Heavy-duty diesel and petrol rotary tillers and inter-cultivators",
      "image_url": "https://backend.nexviadls.com/uploads/categories/agro_tillers.webp",
      "products_count": 8,
      "subcategories_count": 2,
      "subcategories": [
        {
          "id": 7,
          "name": "7 HP Heavy Duty Tillers",
          "slug": "7-hp-tillers",
          "products_count": 4
        }
      ]
    }
  ]
}
```

---

### 18.5 DLS Agro Subcategories by Category
List child subcategories under an agricultural category.

- **Method:** `GET` or `POST`
- **URL:** `/api/dls-agro/categories/{idOrSlug}/subcategories` (Alias: `/api/dls-farm/categories/{idOrSlug}/subcategories`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "category": {
    "id": 3,
    "name": "Power Tillers & Weeders",
    "slug": "power-tillers-weeders"
  },
  "subcategories": [
    {
      "id": 7,
      "name": "7 HP Heavy Duty Tillers",
      "slug": "7-hp-tillers",
      "products_count": 4
    }
  ]
}
```

---

### 18.6 DLS Agro All Subcategories
List all agricultural equipment subcategories.

- **Method:** `GET` or `POST`
- **URL:** `/api/dls-agro/subcategories` (Alias: `/api/dls-farm/subcategories`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "count": 6,
  "data": [
    {
      "id": 7,
      "name": "7 HP Heavy Duty Tillers",
      "slug": "7-hp-tillers",
      "parent_category": "Power Tillers & Weeders",
      "products_count": 4
    }
  ]
}
```

---

### 18.7 DLS Agro Subcategory Detail
Single agricultural subcategory detail.

- **Method:** `GET` or `POST`
- **URL:** `/api/dls-agro/subcategories/{idOrSlug}` (Alias: `/api/dls-farm/subcategories/{idOrSlug}`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "id": 7,
    "name": "7 HP Heavy Duty Tillers",
    "slug": "7-hp-tillers",
    "products_count": 4
  }
}
```

---

### 18.8 DLS Agro Products Catalog with Specs & Filters
Search and filter agricultural machinery, sprayers, pumps, and harvesters.

- **Method:** `GET` or `POST`
- **URL:** `/api/dls-agro/products` (Alias: `/api/dls-farm/products`)
- **Auth Required:** No

#### Query Parameters:
| Parameter | Type | Description |
|---|---|---|
| `category_id` | int/slug | Filter by agro category |
| `subcategory_id` | int/slug | Filter by subcategory |
| `search` | string | Keyword search |
| `sort_by` | string | `price_low`, `price_high`, `newest` |

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "total": 14,
  "current_page": 1,
  "products": [
    {
      "id": 22,
      "name": "DLS Agro 7.5 HP Diesel Power Tiller",
      "slug": "dls-agro-7-5hp-power-tiller",
      "model_code": "DLS-TL-750D",
      "mrp": 65000.00,
      "booking_amount": 13000.00,
      "balance_amount": 52000.00,
      "booking_percentage": 20.00,
      "engine_type": "4-Stroke Air Cooled Diesel",
      "power_hp": "7.5 HP",
      "image": "https://backend.nexviadls.com/uploads/products/agro_tiller_750.webp"
    }
  ]
}
```

---

### 18.9 DLS Agro Featured Agricultural Equipment
Fetch top-performing and high-demand agricultural machinery.

- **Method:** `GET` or `POST`
- **URL:** `/api/dls-agro/products/featured` (Alias: `/api/dls-farm/products/featured`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "count": 3,
  "data": [
    {
      "id": 22,
      "name": "DLS Agro 7.5 HP Diesel Power Tiller",
      "mrp": 65000.00,
      "booking_deposit": 13000.00
    }
  ]
}
```

---

### 18.10 DLS Agro Single Product Details
Complete technical specifications (engine displacement, fuel tank capacity, gear speeds, blade width) for farm machinery.

- **Method:** `GET` or `POST`
- **URL:** `/api/dls-agro/products/{idOrSlug}` (Alias: `/api/dls-farm/products/{idOrSlug}`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "DLS Agro product details retrieved successfully.",
  "data": {
    "id": 22,
    "name": "DLS Agro 7.5 HP Diesel Power Tiller",
    "slug": "dls-agro-7-5hp-power-tiller",
    "model_code": "DLS-TL-750D",
    "mrp": 65000.00,
    "booking_percentage": 20.00,
    "booking_amount": 13000.00,
    "balance_amount": 52000.00,
    "specifications": [
      { "name": "Engine Power", "value": "7.5 HP (3600 RPM)" },
      { "name": "Displacement", "value": "296 cc" },
      { "name": "Tilling Width", "value": "1000 mm (Adjustable)" },
      { "name": "Tilling Depth", "value": "150 - 300 mm" },
      { "name": "Starting System", "value": "Electric Key Start & Recoil" }
    ],
    "key_features": [
      "Heavy duty oil bath air filter for dusty field conditions",
      "PTO shaft for water pump and sprayer attachments",
      "Subsidy eligible under state agricultural schemes"
    ],
    "stock": 15
  }
}
```

---

### 18.11 DLS Agro Hero & Section Banners
Hero sliders, promotional banners, and seasonal campaign posters for agricultural machinery.

- **Method:** `GET` or `POST`
- **URL:** `/api/dls-agro/banners` (Aliases: `/api/dls-farm/banners`, `/api/agro/banners`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "DLS Agro banners retrieved successfully.",
  "total": 3,
  "agro_section": {
    "hero_sliders": [
      {
        "id": 11,
        "title": "Modernize Your Farming with DLS Agro",
        "subtitle": "20% Booking Deposit • Next-Day Local DSP Handover",
        "banner_type": "hero",
        "image_url": "https://backend.nexviadls.com/uploads/banners/agro_hero.webp"
      }
    ],
    "promo_banners": [],
    "side_banners": []
  }
}
```

---

### 18.12 DLS Agro Side Banners
Side banners specifically tailored for agricultural machinery category and product pages.

- **Method:** `GET` or `POST`
- **URL:** `/api/dls-agro/banners/side` (Aliases: `/api/dls-farm/banners/side`, `/api/agro/banners/side`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": [
    {
      "id": 12,
      "title": "Government Subsidy Assistance",
      "subtitle": "Contact local DSP center for subsidy paperwork",
      "banner_type": "side",
      "image_url": "https://backend.nexviadls.com/uploads/banners/subsidy_side.webp"
    }
  ]
}
```

---

## 19. Website Timed Popup Lead Capture APIs

These endpoints power the timed lead generation popup that appears after **5 to 10 seconds** on the frontend website. The admin can customize the trigger delay, title, subtitle, button text, and toggle the popup on/off from the Admin Panel.

---

### 19.1 Get Popup Configuration & Delay
Retrieves the active settings configured by the administrator in the Admin Panel.

- **Method:** `GET`
- **URL:** `/api/popup-settings` (Alias: `/api/popup-lead/config`)
- **Auth Required:** No (Public endpoint for frontend)

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Popup configuration retrieved successfully",
  "data": {
    "is_enabled": true,
    "delay_seconds": 7,
    "title": "Get Exclusive Offers & Instant Callback",
    "subtitle": "Enter your name and mobile number. Our product specialist will call you with exclusive pricing and product details.",
    "button_text": "Request Callback Now",
    "success_message": "Thank you! We have received your request. Our specialist will call you shortly.",
    "reappear_hours": 24
  }
}
```

---

### 19.2 Submit Popup Lead Enquiry
Captures customer details (Name, Mobile Number, optional Email/Notes) submitted via the timed popup.

- **Method:** `POST`
- **URL:** `/api/popup-leads` (Aliases: `/api/popup-lead`, `/api/popup-enquiry`, `/api/leads/popup`)
- **Content-Type:** `application/json`
- **Auth Required:** No (Public lead capture)

#### Request Body
```json
{
  "name": "Rahul Sharma",
  "mobile": "9876543210",
  "email": "rahul.sharma@example.com",
  "notes": "Looking for 55-inch LED TV 20% flexi-booking offer",
  "source_url": "https://nexvia.com/products/nexvia-55-inch-ultra-hd-4k-smart-led-tv"
}
```

#### Field Specifications:
| Field | Type | Required | Description |
|---|---|---|---|
| `name` | string | **Yes** | Full name (min: 2, max: 100 chars) |
| `mobile` | string | **Yes** | 10-digit Indian mobile number (`^[6-9]\d{9}$`) |
| `email` | string | No | Customer email address |
| `notes` | string | No | Optional notes or query message |
| `source_url` | string | No | Current browser page URL where popup was submitted |

#### Success Response (`201 Created`)
```json
{
  "status": true,
  "message": "Thank you! We have received your request. Our specialist will call you shortly.",
  "data": {
    "id": 1,
    "name": "Rahul Sharma",
    "mobile": "9876543210",
    "status": "pending",
    "created_at": "2026-10-16 12:07:24"
  }
}
```

#### Validation Error (`422 Unprocessable Content`)
```json
{
  "status": false,
  "message": "Please enter a valid 10-digit mobile number.",
  "errors": {
    "mobile": [
      "Please enter a valid 10-digit mobile number."
    ]
  }
}
```

---

### 19.3 Frontend React / Vanilla JS Quick Integration Guide

#### A. Direct Script Integration (Any HTML / Blade / Website)
Add to your page's `<head>` and before `</body>`:
```html
<link rel="stylesheet" href="http://127.0.0.1:8000/css/nexvia-popup-lead.css">
<script src="http://127.0.0.1:8000/js/nexvia-popup-lead.js" defer></script>
```

#### B. React / Next.js Component (`PopupLeadModal.jsx`)
```jsx
import React, { useState, useEffect } from 'react';

export default function PopupLeadModal({ apiBase = 'http://127.0.0.1:8000' }) {
  const [isOpen, setIsOpen] = useState(false);
  const [config, setConfig] = useState(null);
  const [name, setName] = useState('');
  const [mobile, setMobile] = useState('');
  const [email, setEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const [submitted, setSubmitted] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    if (typeof window === 'undefined') return;
    if (localStorage.getItem('nexvia_popup_lead_submitted') === 'true') return;

    const dismissedUntil = localStorage.getItem('nexvia_popup_lead_dismissed_until');
    if (dismissedUntil && Date.now() < parseInt(dismissedUntil, 10)) return;

    fetch(`${apiBase}/api/popup-settings`)
      .then(res => res.json())
      .then(res => {
        if (res?.data?.is_enabled) {
          setConfig(res.data);
          const delaySec = res.data.delay_seconds || 7;
          const timer = setTimeout(() => setIsOpen(true), delaySec * 1000);
          return () => clearTimeout(timer);
        }
      })
      .catch(() => {});
  }, [apiBase]);

  const handleClose = () => {
    setIsOpen(false);
    const reappearHours = config?.reappear_hours || 24;
    localStorage.setItem('nexvia_popup_lead_dismissed_until', (Date.now() + (reappearHours * 3600 * 1000)).toString());
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!name.trim()) return setError('Please enter your name');
    if (!/^[6-9]\d{9}$/.test(mobile.trim())) return setError('Please enter a valid 10-digit mobile number');

    setLoading(true);
    setError('');

    try {
      const res = await fetch(`${apiBase}/api/popup-leads`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
          name: name.trim(),
          mobile: mobile.trim(),
          email: email.trim() || undefined,
          source_url: window.location.href
        })
      });
      const data = await res.json();
      if (res.ok && data.status) {
        setSubmitted(true);
        localStorage.setItem('nexvia_popup_lead_submitted', 'true');
        setTimeout(() => setIsOpen(false), 3000);
      } else {
        setError(data.message || 'Submission failed');
      }
    } catch (err) {
      setError('Connection error. Please try again.');
    } finally {
      setLoading(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div style={{ position: 'fixed', inset: 0, backgroundColor: 'rgba(15,23,42,0.7)', backdropFilter: 'blur(5px)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 999999 }}>
      <div style={{ background: '#fff', borderRadius: '20px', padding: '30px', maxWidth: '420px', width: '90%', position: 'relative' }}>
        <button onClick={handleClose} style={{ position: 'absolute', top: '15px', right: '15px', border: 'none', background: '#f1f5f9', borderRadius: '50%', width: '32px', height: '32px', cursor: 'pointer' }}>&times;</button>
        {submitted ? (
          <div style={{ textAlign: 'center', padding: '20px' }}>
            <h3 style={{ color: '#10b981' }}>✓ Request Received!</h3>
            <p>{config?.success_message || 'Thank you! Our expert will call you shortly.'}</p>
          </div>
        ) : (
          <form onSubmit={handleSubmit}>
            <h3 style={{ margin: '8px 0', fontSize: '20px', fontWeight: '800' }}>{config?.title || 'Get Instant Callback'}</h3>
            <p style={{ fontSize: '13px', color: '#64748b' }}>{config?.subtitle || 'Leave your name and number to get special deals.'}</p>
            {error && <div style={{ color: '#ef4444', fontSize: '13px', marginBottom: '10px' }}>{error}</div>}
            <input type="text" placeholder="Your Full Name *" value={name} onChange={e => setName(e.target.value)} style={{ width: '100%', height: '44px', marginBottom: '12px' }} required />
            <input type="tel" placeholder="10-digit Mobile Number *" value={mobile} maxLength={10} onChange={e => setMobile(e.target.value.replace(/\D/g, ''))} style={{ width: '100%', height: '44px', marginBottom: '12px' }} required />
            <button type="submit" disabled={loading} style={{ width: '100%', height: '46px', background: '#7c3aed', color: '#fff', border: 'none', borderRadius: '10px', fontWeight: '700' }}>
              {loading ? 'Submitting...' : (config?.button_text || 'Request Callback')}
            </button>
          </form>
        )}
      </div>
    </div>
  );
}
```

---

## 20. CMS Dynamic Pages & Legal Policy APIs

The `PageApiController` powers all institutional pages, privacy compliance documents, terms of service, warranty rules, and contact inquiry submissions.

---

### 20.1 Get Privacy Policy
Retrieve the official, active Privacy Policy document including HTML content and structured policy points.

- **Method:** `GET` or `POST`
- **URL:** `/api/privacy-policy` (Alias: `/api/customer/privacy-policy`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Privacy policy retrieved successfully.",
  "data": {
    "id": 1,
    "title": "Privacy Policy",
    "slug": "privacy-policy",
    "excerpt": "Official Privacy Policy regarding user data, telemetry, and payments.",
    "content": "<p>At NEXVIA Mobility, we value your privacy and trust...</p>",
    "points": [
      "We collect mobile numbers and delivery addresses solely for booking and fulfillment.",
      "Financial data is processed securely through RBI-compliant payment gateways.",
      "We never sell or rent your personal data to third-party marketing brokers."
    ],
    "meta_title": "Privacy Policy | NEXVIA Mobility",
    "meta_description": "Read our comprehensive privacy policy regarding your personal data.",
    "meta_keywords": "privacy, policy, data protection, nexvia",
    "sort_order": 1,
    "updated_at": "2026-10-16T09:00:00+05:30"
  }
}
```

---

### 20.2 Get Terms & Conditions
Retrieve the complete Terms and Conditions governing 20% Flexi-Bookings, 60-day balance settlement, DSP deliveries, and warranty terms.

- **Method:** `GET` or `POST`
- **URL:** `/api/terms-and-conditions` (Aliases: `/api/terms-conditions`, `/api/customer/terms-and-conditions`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Terms and Conditions retrieved successfully.",
  "data": {
    "id": 2,
    "title": "Terms and Conditions",
    "slug": "terms-and-conditions",
    "excerpt": "Terms governing customer bookings, payments, and warranties.",
    "content": "<p>These terms govern your purchase and flexi-booking on NEXVIA platform...</p>",
    "points": [
      "A 20% initial deposit confirms a flexi-booking and holds product price for 60 days.",
      "The remaining 80% balance must be settled within 60 days of booking confirmation.",
      "Delivery and Digital Delivery Challan (DC) are issued strictly upon 100% full balance payment.",
      "Physical handover requires verification of the 6-digit Customer Delivery OTP."
    ],
    "updated_at": "2026-10-16T09:00:00+05:30"
  }
}
```

---

### 20.3 Get Refund & Cancellation Policy
Retrieve the official policy detailing non-cancellable token deposits and stored product credit reallocation rights.

- **Method:** `GET` or `POST`
- **URL:** `/api/refund-policy` (Alias: `/api/customer/refund-policy`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Refund policy retrieved successfully.",
  "data": {
    "id": 3,
    "title": "Refund & Cancellation Policy",
    "slug": "refund-policy",
    "excerpt": "Rules regarding 20% booking deposits and 100% stored credit protection.",
    "content": "<p>NEXVIA operates a 100% Value Protection Guarantee...</p>",
    "points": [
      "Once confirmed, the 20% booking deposit is non-refundable in cash.",
      "If balance is unpaid after 60 days, 100% of your paid amount is safely stored.",
      "Customers can reallocate their stored paid amount at any time to buy any other product in catalog."
    ],
    "updated_at": "2026-10-16T09:00:00+05:30"
  }
}
```

---

### 20.4 Get About Us Page
Retrieve corporate overview, leadership mission, and company background.

- **Method:** `GET` or `POST`
- **URL:** `/api/about-us` (Alias: `/api/customer/about-us`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "About Us retrieved successfully.",
  "data": {
    "id": 4,
    "title": "About NEXVIA",
    "slug": "about-us",
    "content": "<p>NEXVIA DLS is a next-generation mobility and electronics brand...</p>",
    "points": [
      "Over 100+ Authorised Delivery & Service Partner centers across India.",
      "Pioneers of the 20% Flexi-Booking with 60-Day zero interest balance model."
    ]
  }
}
```

---

### 20.5 Get Contact Us Details
Retrieve headquarters address, customer care helpline, official email, and operational timings.

- **Method:** `GET`
- **URL:** `/api/contact-us` (Alias: `/api/customer/contact-us`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Contact Us retrieved successfully.",
  "data": {
    "id": 5,
    "title": "Contact Us",
    "slug": "contact-us",
    "content": "<p>Get in touch with our national support team...</p>",
    "points": [
      "Helpline: +91-1800-NEXVIA (Mon-Sat 9AM-8PM)",
      "Customer Care Email: nexviadls@gmail.com",
      "Corporate Headquarters: Pune, Maharashtra, India"
    ]
  }
}
```

---

### 20.6 Submit Contact Us Inquiry
Allows visitors and customers to submit an inquiry. Automatically triggers dual notification emails: one to the administrator and an auto-acknowledgement reply to the sender.

- **Method:** `POST`
- **URL:** `/api/contact-us`
- **Content-Type:** `application/json`
- **Auth Required:** No

#### Request Body
```json
{
  "name": "Rahul Sharma",
  "email": "rahul.sharma@example.com",
  "phone": "9876543210",
  "subject": "Bulk Dealership & DSP Partnership Inquiry",
  "message": "We operate a 2500 sqft facility in Pune and would like to apply for the Authorised DSP Partner program."
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Your message has been sent successfully. We will get back to you shortly!"
}
```

---

### 20.7 List All Dynamic CMS Pages
Fetch list of all active pages configured in the CMS for dynamic navigation menus and footer links.

- **Method:** `GET` or `POST`
- **URL:** `/api/pages` (Alias: `/api/customer/pages`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Pages list retrieved successfully.",
  "total": 5,
  "data": [
    {
      "id": 1,
      "title": "Privacy Policy",
      "slug": "privacy-policy",
      "excerpt": "Official Privacy Policy regarding user data and telemetry.",
      "points_count": 3,
      "sort_order": 1,
      "updated_at": "2026-10-16T09:00:00+05:30"
    },
    {
      "id": 2,
      "title": "Terms and Conditions",
      "slug": "terms-and-conditions",
      "excerpt": "Terms governing customer bookings, payments, and warranties.",
      "points_count": 4,
      "sort_order": 2,
      "updated_at": "2026-10-16T09:00:00+05:30"
    }
  ]
}
```

---

### 20.8 Get Single CMS Page by Slug or ID
Fetch any dynamic CMS page by its slug or numeric database ID.

- **Method:** `GET` or `POST`
- **URL:** `/api/pages/{slugOrId}` (Alias: `/api/customer/pages/{slugOrId}`)
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Privacy Policy retrieved successfully.",
  "data": {
    "id": 1,
    "title": "Privacy Policy",
    "slug": "privacy-policy",
    "content": "<p>Full content of page...</p>",
    "points": [
      "We collect mobile numbers and delivery addresses solely for booking and fulfillment."
    ],
    "meta_title": "Privacy Policy | NEXVIA",
    "meta_description": "Official policy documentation"
  }
}
```

#### Error Response (`404 Not Found`)
```json
{
  "status": false,
  "message": "Page 'unknown-page' not found or is currently inactive."
}
```

---

## 21. System Diagnostics, Automated Deployment & Utilities

---

### 21.1 API Root Health Check
Verify API server availability and liveness.

- **Method:** `GET`
- **URL:** `/api/`
- **Auth Required:** No

#### Success Response (`200 OK`)
```json
{
  "message": "NEXVIA API is running"
}
```

---

### 21.2 Automated Deployment Webhook
Webhook endpoint triggered by GitHub / Bitbucket CI/CD or deployment triggers to automatically pull latest Git commits, run database migrations, and clear cache.

- **Method:** `GET` or `POST`
- **URL:** `/api/deploy` (Alias: `/api/deploy.php`)
- **Query Parameter / Header:** `key=MySecretKey123!`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "method": "POST",
  "output": "Already up to date.\nNothing to migrate.\nConfiguration cache cleared successfully.\nApplication cache cleared successfully.",
  "time": "2026-10-16 15:30:00"
}
```

#### Error Response (`403 Forbidden`)
```json
{
  "status": false,
  "message": "Unauthorized access"
}
```

---

### 21.3 Live Mail Diagnostics & Test Email
Performs end-to-end network socket diagnostics (port 587, port 465 SSL, port 25), DNS host resolution, transport verification, and sends a test email to the requested recipient.

- **Method:** `GET` or `POST`
- **URL:** `/api/test-email`
- **Query Parameters / Body:**
  - `to` (string, optional) — Recipient email address (default: `config('mail.from.address')`).
  - `mailer` (string, optional) — Dynamic mailer override (e.g., `smtp`, `sendmail`).
  - `port` (int, optional) — Dynamic port override (e.g., `587`, `465`).

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Test email sent successfully to rahul.sharma@example.com in 420.5ms!",
  "recipient": "rahul.sharma@example.com",
  "duration_ms": 420.5,
  "config": {
    "default_mailer": "smtp",
    "smtp_host": "smtp.gmail.com",
    "smtp_port": 587,
    "smtp_encryption": "tls",
    "smtp_username": "nexviadls@gmail.com",
    "password_set": true,
    "from_address": "nexviadls@gmail.com",
    "from_name": "NEXVIA",
    "queue_connection": "sync"
  },
  "network_checks": {
    "resolved_ip": "142.251.10.108",
    "port_587": { "connected": true, "error": null },
    "port_465_ssl": { "connected": true, "error": null },
    "local_port_25_exim": { "connected": false, "error": "Connection refused (61)" }
  },
  "advice": "Email was accepted by the transport driver. Check recipient inbox (and spam/promotions folder)."
}
```

#### Error Response (`500 Internal Server Error`)
```json
{
  "status": false,
  "message": "Failed to send test email.",
  "error": "Connection could not be established with host smtp.gmail.com :stream_socket_client(): Unable to connect to tcp://smtp.gmail.com:587 (Network is unreachable)",
  "error_type": "Symfony\\Component\\Mailer\\Exception\\TransportException",
  "duration_ms": 3005.2,
  "advice": "Hosting firewall blocks outbound SMTP connection on this port. Try switching to port 465 (SSL) or use sendmail driver."
}
```

---

*Documentation Version: 2.5 (Comprehensive All-API Release) — NEXVIA Mobility & Electronics Ltd.*