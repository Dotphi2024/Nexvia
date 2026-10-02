# NEXVIA Mobility & Electronics — Complete API Reference Manual

> **Base URL:** `https://your-domain.com/api` (Local: `http://127.0.0.1:8000/api`)  
> **API Version:** v1 / Direct REST  
> **Content-Type:** `application/json` / `multipart/form-data` (for file uploads)  
> **Authentication Scheme:** `Authorization: Bearer <token>` or `token: <token>` header or `user_id` parameter.

---

## Table of Contents

1. [Authentication & Account APIs](#1-authentication--account-apis)
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
3. [Home Sliders & Banners APIs](#3-home-sliders--banners-apis)
   - [3.1 Get Home Banners](#31-get-home-banners)
   - [3.2 Record Banner Click / Tap](#32-record-banner-click--tap)
4. [Categories & Reward Progression Slabs APIs](#4-categories--reward-progression-slabs-apis)
   - [4.1 List All Categories](#41-list-all-categories)
   - [4.2 Single Category Detail & Products](#42-single-category-detail--products)
5. [Products & Search APIs](#5-products--search-apis)
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
7. [Cart Management APIs](#7-cart-management-apis)
   - [7.1 Get Cart Items & Financial Summary](#71-get-cart-items--financial-summary)
   - [7.2 Add Item to Cart](#72-add-item-to-cart)
   - [7.3 Update Cart Item Quantity / Color](#73-update-cart-item-quantity--color)
   - [7.4 Remove Item from Cart](#74-remove-item-from-cart)
   - [7.5 Clear Cart](#75-clear-cart)
8. [Checkout Calculation & Gateway Payment APIs](#8-checkout-calculation--gateway-payment-apis)
   - [8.1 Compute Checkout & Tax Breakdown](#81-compute-checkout--tax-breakdown)
   - [8.2 Create Gateway Payment Order (Razorpay)](#82-create-gateway-payment-order-razorpay)
   - [8.3 Verify Payment Callback](#83-verify-payment-callback)
9. [Flexi-Bookings & 60-Day Balance Engine APIs](#9-flexi-bookings--60-day-balance-engine-apis)
   - [9.1 List User Bookings](#91-list-user-bookings)
   - [9.2 Create Confirmed 20% Flexi-Booking](#92-create-confirmed-20-flexi-booking)
   - [9.3 Get Booking Detail & Receipt](#93-get-booking-detail--receipt)
   - [9.4 Pay Remaining 80% Balance (Cash / Wallet)](#94-pay-remaining-80-balance-cash--wallet)
   - [9.5 Initiate Booking Transfer](#95-initiate-booking-transfer)
   - [9.6 Confirm Booking Transfer via OTP](#96-confirm-booking-transfer-via-otp)
10. [Multi-Item Orders & Delivery Tracking APIs](#10-multi-item-orders--delivery-tracking-apis)
    - [10.1 Multi-Item Order Checkout](#101-multi-item-order-checkout)
    - [10.2 Track 7-Stage Order Delivery](#102-track-7-stage-order-delivery)
11. [Warranty & Service Support APIs](#11-warranty--service-support-apis)
    - [11.1 List Customer Warranties](#111-list-customer-warranties)
    - [11.2 Create Service / Repair Ticket](#112-create-service--repair-ticket)
    - [11.3 List Customer Service Tickets](#113-list-customer-service-tickets)
    - [11.4 Schedule / Rate Installation](#114-schedule--rate-installation)
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

---

## 1. Authentication & Account APIs

### 1.1 Register Customer
Create a new customer account and generate unique referral code and API token.

- **Method:** `POST`
- **URL:** `/api/auth/register` or `/api/customer/register`
- **Auth Required:** No

#### Request Body
```json
{
  "fullName": "Rahul Sharma",
  "phone": "9876543210",
  "email": "rahul.sharma@example.com",
  "password": "optional_secure_password",
  "referral_code": "NEX12345",
  "pincode": "411001",
  "city": "Pune",
  "state": "Maharashtra",
  "fcm_token": "fcm_device_token_here",
  "terms_accepted": true,
  "terms_version": "v1.0"
}
```

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
    "fcm_token": "fcm_device_token_here",
    "referred_by": {
      "id": 3,
      "name": "Anil Kumar",
      "referral_code": "NEX12345"
    }
  }
}
```

#### Error Response (`422 Unprocessable Entity`)
```json
{
  "status": false,
  "message": "Validation error",
  "errors": {
    "phone": ["This phone number is already registered."]
  }
}
```

---

### 1.2 Login Customer (Passwordless / Phone)
Instant login via registered phone number or email. Returns authentication bearer token.

- **Method:** `POST`
- **URL:** `/api/auth/login` or `/api/customer/login`
- **Auth Required:** No

#### Request Body
```json
{
  "phone": "9876543210",
  "fcm_token": "fcm_token_optional"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Login successful",
  "token": "9a8b7c6d5e4f3a2b1c0d9e8f7a6b5c4d3e2f1a0b9c8d7e6f5a4b3c2d1e0f9a8",
  "data": {
    "id": 12,
    "fullName": "Rahul Sharma",
    "name": "Rahul Sharma",
    "phone": "9876543210",
    "email": "rahul.sharma@example.com",
    "referral_code": "NEX98B2A1",
    "referral_url": "https://your-domain.com/ref/NEX98B2A1",
    "is_self_dealer": true,
    "self_dealer_code": "SD-12-PUN",
    "self_dealer_status": "active",
    "wallet_balance": 4500.00,
    "city": "Pune",
    "state": "Maharashtra",
    "pincode": "411001",
    "avatarUrl": "https://your-domain.com/customer_pics/avatar.jpg",
    "status": "active"
  }
}
```

---

### 1.3 Send OTP
Send a 6-digit verification code to the customer's registered WhatsApp / SMS.

- **Method:** `POST`
- **URL:** `/api/auth/send-otp` or `/api/customer/send-otp`
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
  "message": "OTP sent successfully.",
  "phone": "9876543210",
  "otp": null,
  "data": {
    "phone": "9876543210"
  }
}
```

---

### 1.4 Verify OTP
Verify 6-digit code and authenticate the customer.

- **Method:** `POST`
- **URL:** `/api/auth/verify-otp` or `/api/customer/verify-otp`
- **Auth Required:** No

#### Request Body
```json
{
  "phone": "9876543210",
  "otpCode": "123456"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "OTP verified. Login successful!",
  "token": "7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f4a5b6c7d8e9f0a1b2c3d4e5f6a7b8",
  "data": {
    "id": 12,
    "fullName": "Rahul Sharma",
    "phone": "9876543210",
    "email": "rahul.sharma@example.com",
    "referral_code": "NEX98B2A1",
    "wallet_balance": 4500.00,
    "status": "active"
  }
}
```

---

### 1.5 Refresh Token
Generate a new API token using an active or existing token.

- **Method:** `POST`
- **URL:** `/api/auth/refresh-token` or `/api/customer/refresh-token`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body:**
```json
{
  "refreshToken": "existing_api_token"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Token refreshed successfully.",
  "token": "new_refreshed_api_token_value",
  "refreshToken": "new_refreshed_api_token_value"
}
```

---

### 1.6 Forgot Password OTP
Dispatch a password reset OTP to registered WhatsApp number.

- **Method:** `POST`
- **URL:** `/api/auth/forgot-password` or `/api/customer/forgot-password`
- **Request Body:**
```json
{
  "email": "rahul.sharma@example.com"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Password reset OTP sent to your registered WhatsApp phone number.",
  "phone": "9876543210"
}
```

---

### 1.7 Resend OTP
Resend OTP with 60-second rate-limit guard.

- **Method:** `POST`
- **URL:** `/api/auth/resend-otp` or `/api/customer/resend-otp`
- **Request Body:**
```json
{
  "phone": "9876543210"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "OTP resent to your WhatsApp number.",
  "phone": "9876543210"
}
```

#### Rate Limit Response (`429 Too Many Requests`)
```json
{
  "status": false,
  "message": "Please wait 45 seconds before requesting a new OTP.",
  "retry_after": 45
}
```

---

### 1.8 Customer Logout
Invalidate active API token and clear user sessions.

- **Method:** `POST` or `GET`
- **URL:** `/api/auth/logout` or `/api/customer/logout` or `/api/logout`
- **Request Body:**
```json
{
  "user_id": 12
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "User logged out successfully.",
  "user_id": 12,
  "data": {
    "user_id": 12,
    "name": "Rahul Sharma",
    "phone": "9876543210"
  }
}
```

---

## 2. Customer Profile & Delivery Address APIs

### 2.1 Get Customer Profile
Fetch logged-in customer's details, default shipping address, and saved address list.

- **Method:** `GET` or `POST`
- **URL:** `/api/user/profile` or `/api/customer/profile`
- **Headers:** `Authorization: Bearer <token>`
- **Query / Body (Fallback):** `?customer_id=12`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Profile fetched successfully.",
  "token": "active_token_here",
  "data": {
    "id": 12,
    "name": "Rahul Sharma",
    "fullName": "Rahul Sharma",
    "email": "rahul.sharma@example.com",
    "phone": "9876543210",
    "referral_code": "NEX98B2A1",
    "is_self_dealer": true,
    "self_dealer_status": "active",
    "wallet_balance": 4500.00,
    "address": "Flat 402, Green Avenue, FC Road",
    "city": "Pune",
    "state": "Maharashtra",
    "pincode": "411001",
    "avatarUrl": "https://your-domain.com/customer_pics/customer_pic_12.jpg",
    "default_address": {
      "id": 5,
      "user_id": 12,
      "name": "Rahul Sharma",
      "phone": "9876543210",
      "street": "Flat 402, Green Avenue, FC Road",
      "city": "Pune",
      "state": "Maharashtra",
      "pincode": "411001",
      "is_default": true
    },
    "saved_addresses": [
      {
        "id": 5,
        "name": "Rahul Sharma",
        "phone": "9876543210",
        "street": "Flat 402, Green Avenue, FC Road",
        "city": "Pune",
        "state": "Maharashtra",
        "pincode": "411001",
        "is_default": true
      }
    ]
  }
}
```

---

### 2.2 Update Profile & Avatar
Update profile text fields or upload new profile photo.

- **Method:** `PUT` or `POST`
- **URL:** `/api/user/profile` or `/api/customer/update-profile`
- **Headers:** `Authorization: Bearer <token>`
- **Content-Type:** `multipart/form-data` or `application/json`

#### Request Body (Multipart / Form / JSON)
```
fullName: Rahul S. Sharma
email: rahul.updated@example.com
address: Flat 501, Blue Towers
city: Pune
state: Maharashtra
pincode: 411004
profile_pic: [FILE ATTACHMENT: avatar.jpg]
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Profile updated successfully.",
  "token": "token_here",
  "data": {
    "id": 12,
    "name": "Rahul S. Sharma",
    "fullName": "Rahul S. Sharma",
    "email": "rahul.updated@example.com",
    "phone": "9876543210",
    "address": "Flat 501, Blue Towers",
    "city": "Pune",
    "state": "Maharashtra",
    "pincode": "411004",
    "avatarUrl": "https://your-domain.com/customer_pics/customer_pic_1741580000.jpg"
  }
}
```

---

### 2.3 Update FCM Device Token
Register device token for push notifications and location.

- **Method:** `POST`
- **URL:** `/api/customer/fcm-token`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "fcm_token": "eX_ample_fcm_token_device_string",
  "latitude": 18.5204,
  "longitude": 73.8567
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "FCM token updated successfully."
}
```

---

### 2.4 Get Saved Addresses
- **Method:** `GET`
- **URL:** `/api/user/addresses` or `/api/addresses` or `/api/customer/addresses`
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Addresses retrieved successfully.",
  "total": 2,
  "data": [
    {
      "id": 5,
      "user_id": 12,
      "name": "Rahul Sharma (Home)",
      "phone": "9876543210",
      "street": "Flat 402, Green Avenue, FC Road",
      "city": "Pune",
      "state": "Maharashtra",
      "pincode": "411001",
      "is_default": true,
      "created_at": "2026-03-01T10:00:00.000000Z"
    },
    {
      "id": 8,
      "user_id": 12,
      "name": "Rahul Sharma (Office)",
      "phone": "9876543210",
      "street": "Tech Park, Building C, Viman Nagar",
      "city": "Pune",
      "state": "Maharashtra",
      "pincode": "411014",
      "is_default": false,
      "created_at": "2026-03-05T12:30:00.000000Z"
    }
  ]
}
```

---

### 2.5 Add or Update Address
- **Method:** `POST`
- **URL:** `/api/user/addresses` or `/api/addresses`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "address_id": null,
  "name": "Rahul Sharma",
  "phone": "9876543210",
  "street": "Flat 402, Green Avenue, FC Road",
  "city": "Pune",
  "state": "Maharashtra",
  "pincode": "411001",
  "isDefault": true
}
```

#### Success Response (`201 Created` / `200 OK`)
```json
{
  "status": true,
  "message": "Address saved successfully.",
  "data": {
    "id": 9,
    "user_id": 12,
    "name": "Rahul Sharma",
    "phone": "9876543210",
    "street": "Flat 402, Green Avenue, FC Road",
    "city": "Pune",
    "state": "Maharashtra",
    "pincode": "411001",
    "is_default": true,
    "created_at": "2026-03-10T10:00:00.000000Z"
  }
}
```

---

### 2.6 Delete Address
- **Method:** `DELETE` or `POST`
- **URL:** `/api/user/addresses/{id}` or `/api/addresses/delete`
- **Headers:** `Authorization: Bearer <token>`
- **Request Body (if POST):** `{"address_id": 9}`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Address removed successfully."
}
```

---

## 3. Home Sliders & Banners APIs

### 3.1 Get Home Banners
Fetches hero sliders and middle promo banners configured for the Home Index page.

- **Method:** `GET`
- **URL:** `/api/home/banners` or `/api/banners` or `/api/home-banners`
- **Query Parameters:** `?type=hero` or `?type=promo` (Optional)

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Home section banners retrieved successfully.",
  "total": 3,
  "home_section": {
    "hero_sliders": [
      {
        "id": 1,
        "title": "NEXVIA Falcon EV",
        "subtitle": "Book Now with 20% Advance & Get 60 Days Balance Window",
        "badge_text": "⚡ ELECTRIC SCOOTER",
        "position": "hero",
        "banner_type": "hero",
        "target_type": "product",
        "target_id": "nexvia-falcon-electric-scooter",
        "link_url": "/products/nexvia-falcon-electric-scooter",
        "button_text": "Book for ₹17,999",
        "image_url": "https://your-domain.com/uploads/banners/hero_1.jpg",
        "sort_order": 1,
        "clicks_count": 142
      }
    ],
    "promo_banners": [
      {
        "id": 2,
        "title": "Refer & Earn Up To 20% Product Credit",
        "subtitle": "Become a Self Dealer with any EV booking",
        "badge_text": "🔥 SELF DEALER PROGRAM",
        "position": "promo",
        "banner_type": "promo",
        "target_type": "category",
        "target_id": "electric-vehicles",
        "link_url": "/categories/electric-vehicles",
        "button_text": "Explore Program",
        "image_url": "https://your-domain.com/uploads/banners/promo_1.jpg",
        "sort_order": 2,
        "clicks_count": 89
      }
    ]
  },
  "data": [ ... ]
}
```

---

### 3.2 Record Banner Click / Tap
Increment click analytics when a user taps a banner.

- **Method:** `POST`
- **URL:** `/api/banners/{id}/click`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Click recorded."
}
```

---

## 4. Categories & Reward Progression Slabs APIs

### 4.1 List All Categories
Retrieve categories with product counts, referral reward slabs (10% to 20%), and user-specific stage progression if authenticated.

- **Method:** `GET` or `POST`
- **URL:** `/api/categories` or `/api/customer/categories`
- **Headers (Optional):** `Authorization: Bearer <token>`
- **Query Parameters:**
  - `search` (string): Keyword filter
  - `referral_eligible` (bool): Filter referral categories (`true`/`false`)

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Categories retrieved successfully.",
  "total": 4,
  "data": [
    {
      "id": 1,
      "name": "Electric Vehicles",
      "slug": "electric-vehicles",
      "type": "EV",
      "referral_category_code": "EV",
      "category_code": "EV",
      "referral_code": "EV",
      "referral_eligible": true,
      "commission_percentage": 20.00,
      "reward_slab": "10% → 20% Product Credit",
      "starting_reward_pct": 10.00,
      "max_reward_pct": 20.00,
      "stages_cycle": [10.00, 12.00, 15.00, 18.00, 20.00],
      "stages_info": [
        {
          "stage": 1,
          "reward_percentage": 10.00,
          "is_current": false,
          "is_completed": true,
          "label": "Stage 1: 10% Product Credit"
        },
        {
          "stage": 2,
          "reward_percentage": 12.00,
          "is_current": true,
          "is_completed": false,
          "label": "Stage 2: 12% Product Credit"
        },
        {
          "stage": 3,
          "reward_percentage": 15.00,
          "is_current": false,
          "is_completed": false,
          "label": "Stage 3: 15% Product Credit"
        },
        {
          "stage": 4,
          "reward_percentage": 18.00,
          "is_current": false,
          "is_completed": false,
          "label": "Stage 4: 18% Product Credit"
        },
        {
          "stage": 5,
          "reward_percentage": 20.00,
          "is_current": false,
          "is_completed": false,
          "label": "Stage 5: 20% Product Credit"
        }
      ],
      "user_progress": {
        "customer_referral_code": "NEX98B2A1",
        "referral_share_code": "NEX98B2A1",
        "current_stage": 2,
        "current_incentive_pct": 12.00,
        "next_stage": 3,
        "next_incentive_pct": 15.00,
        "cycle_number": 1,
        "referrals_in_cycle": 1,
        "total_referrals_all_time": 1,
        "is_final_stage": false,
        "referral_share_link": "https://your-domain.com/register?ref=NEX98B2A1&cat=EV",
        "referral_share_message": "Shop Electric Vehicles on NEXVIA and get exclusive benefits using my referral code: NEX98B2A1"
      },
      "description": "High performance smart electric two wheelers.",
      "image": "https://your-domain.com/uploads/categories/ev.jpg",
      "imageUrl": "https://your-domain.com/uploads/categories/ev.jpg",
      "products_count": 6
    }
  ]
}
```

---

### 4.2 Single Category Detail & Products
Get single category information and product items.

- **Method:** `GET`
- **URL:** `/api/categories/{idOrSlug}` (e.g., `/api/categories/electric-vehicles` or `/api/categories/1` or `/api/categories/EV`)

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Category retrieved successfully.",
  "data": {
    "id": 1,
    "name": "Electric Vehicles",
    "slug": "electric-vehicles",
    "referral_category_code": "EV",
    "reward_slab": "10% → 20% Product Credit",
    "products_count": 2,
    "products": [
      {
        "id": 101,
        "name": "NEXVIA Falcon EV",
        "slug": "nexvia-falcon-ev",
        "model_code": "NEX-FALCON",
        "mrp": 89999.00,
        "booking_amount": 17999.80,
        "eligible_referral_value": 89999.00,
        "referral_eligible": true,
        "self_dealer_eligible": true,
        "main_image": "https://your-domain.com/uploads/products/falcon.jpg",
        "category_code": "EV"
      }
    ]
  }
}
```

---

## 5. Products & Search APIs

### 5.1 Paginated Products Catalog
Fetch products with category filters, sorting, and pagination.

- **Method:** `GET` or `POST`
- **URL:** `/api/products` or `/api/customer/products`
- **Query / Body Parameters:**
  - `cat_id` / `category_id` / `category`: Filter by ID or Slug (e.g., `electric-vehicles` or `1`)
  - `search`: Keyword string
  - `sortBy`: `price_low`, `price_high`, `featured`, `popular`
  - `page`: Page number (Default: `1`)
  - `limit` / `per_page`: Items per page (Default: `10`, Max: `100`)

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Products retrieved successfully.",
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1,
    "has_more": false
  },
  "data": [
    {
      "id": 101,
      "name": "NEXVIA Falcon EV Scooter",
      "slug": "nexvia-falcon-ev-scooter",
      "model_code": "NEX-FLC-01",
      "sku": "SKU-FLC-RED",
      "category": {
        "id": 1,
        "name": "Electric Vehicles",
        "slug": "electric-vehicles",
        "referral_category_code": "EV"
      },
      "mrp": 89999.00,
      "booking_percentage": 20.00,
      "booking_amount": 17999.80,
      "balance_amount": 71999.20,
      "stock": 15,
      "main_image": "https://your-domain.com/uploads/products/falcon_main.jpg",
      "images": [
        "https://your-domain.com/uploads/products/falcon_main.jpg",
        "https://your-domain.com/uploads/products/falcon_side.jpg"
      ],
      "offer_text": "20% Advance Booking • 60-Day Balance Window",
      "is_featured": true,
      "status": "active",
      "eligible_referral_value": 89999.00,
      "referral_eligible": true,
      "self_dealer_eligible": true,
      "self_dealer_benefit": {
        "activates_self_dealer": true,
        "activation_points_pct": 20.00,
        "activation_points_value": 17999.80
      }
    }
  ]
}
```

---

### 5.2 Single Product Details & Specifications
Returns detailed product specs, pricing calculation (20% token, 80% balance due in 60 days), warranty, and delivery information.

- **Method:** `GET`
- **URL:** `/api/products/{idOrSlug}` or `/api/products/detail/{idOrSlug}` (e.g., `/api/products/nexvia-falcon-ev-scooter` or `/api/products/101`)

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Product full details & specifications retrieved successfully.",
  "data": {
    "id": 101,
    "name": "NEXVIA Falcon EV Scooter",
    "slug": "nexvia-falcon-ev-scooter",
    "model_code": "NEX-FLC-01",
    "sku": "SKU-FLC-RED",
    "category": {
      "id": 1,
      "name": "Electric Vehicles",
      "slug": "electric-vehicles",
      "referral_category_code": "EV"
    },
    "mrp": 89999.00,
    "booking_percentage": 20.00,
    "booking_amount": 17999.80,
    "balance_amount": 71999.20,
    "balance_due_days": 60,
    "pricing": {
      "mrp": 89999.00,
      "booking_percentage": 20.00,
      "booking_amount": 17999.80,
      "balance_amount": 71999.20,
      "balance_due_days": 60,
      "currency": "INR",
      "currency_symbol": "₹",
      "pricing_summary": "Pay ₹17,999.80 (20%) now, pay balance ₹71,999.20 within 60 days."
    },
    "referral_eligible": true,
    "self_dealer_eligible": true,
    "eligible_referral_value": 89999.00,
    "self_dealer_benefit": {
      "activates_self_dealer": true,
      "activation_points_pct": 20.00,
      "activation_points_value": 17999.80,
      "banner_title": "SELF DEALER BENEFIT",
      "banner_text": "Book this product and become an eligible Self Dealer. Earn ₹17,999.80 (20%) in activation points!"
    },
    "stock": 15,
    "in_stock": true,
    "stock_status": "In Stock",
    "main_image": "https://your-domain.com/uploads/products/falcon_main.jpg",
    "gallery": [
      "https://your-domain.com/uploads/products/falcon_main.jpg",
      "https://your-domain.com/uploads/products/falcon_angle.jpg"
    ],
    "video_url": "https://www.youtube.com/watch?v=example",
    "key_features": [
      "120 KM Range on Single Charge",
      "Fast Charging: 0-80% in 90 Minutes",
      "Regenerative Braking with Disc Brakes",
      "Digital Smart Touch Dashboard"
    ],
    "specifications": [
      { "name": "Battery Capacity", "value": "3.5 kWh Lithium-Ion" },
      { "name": "Top Speed", "value": "85 km/h" },
      { "name": "Motor Power", "value": "3000W BLDC Hub Motor" },
      { "name": "Brakes", "value": "Front & Rear CBS Disc Brakes" }
    ],
    "warranty_info": "3 Years Comprehensive Battery & Motor Warranty",
    "installation_info": "Free Doorstep Assembly & Home Demo Included",
    "delivery_info": "Free Express Dispatch within 5-7 business days",
    "offer_text": "Special ₹5,000 Early Bird Cashback included."
  }
}
```

---

### 5.3 Product Search & Advanced Filtering
All-in-one product search endpoint supporting any combination of filters.

- **Method:** `GET` or `POST`
- **URL:** `/api/products/search` or `/api/customer/products/search`
- **Supported Parameters (Query / JSON):**
  - `q` / `search`: Keyword string
  - `category` / `category_id`: Category ID or Slug
  - `min_price` & `max_price`: Price filter
  - `referral_eligible`: `true`/`false`
  - `self_dealer_eligible`: `true`/`false`
  - `is_featured`: `true`/`false`
  - `in_stock`: `true`/`false`
  - `sortBy`: `price_low`, `price_high`, `latest`, `name_asc`
  - `page` & `limit`: Pagination parameters

#### Request Example (JSON POST)
```json
{
  "q": "Falcon",
  "category": "electric-vehicles",
  "min_price": 50000,
  "max_price": 100000,
  "referral_eligible": true,
  "sortBy": "price_low",
  "page": 1,
  "limit": 20
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Search results retrieved successfully.",
  "total": 1,
  "applied_filters": {
    "query": "Falcon",
    "category": "electric-vehicles",
    "min_price": 50000,
    "max_price": 100000,
    "referral_eligible": true,
    "sort_by": "price_low"
  },
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1,
    "has_more": false
  },
  "data": [ ... ]
}
```

---

### 5.4 Trending & Lightning Deals
- **Method:** `GET` or `POST`
- **URL:** `/api/products/trending` or `/api/customer/products/trending`
- **Query Parameters:** `?limit=6` (Optional: `?featured_only=true`)

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Trending and lightning deal products retrieved successfully.",
  "total": 2,
  "limit": 6,
  "data": [
    {
      "id": 101,
      "name": "NEXVIA Falcon EV Scooter",
      "mrp": 89999.00,
      "booking_amount": 17999.80,
      "balance_amount": 71999.20,
      "is_trending": true,
      "deal_type": "Lightning Deal",
      "deal_badge": "⚡ LIGHTNING DEAL",
      "deal_tag": "20% Booking Advance Special",
      "stock_status": "In Stock"
    }
  ]
}
```

---

## 6. Wishlist & Favorites APIs

### 6.1 Get Wishlist Items
- **Method:** `GET` or `POST`
- **URL:** `/api/customer/wishlist` or `/api/wishlist`
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Wishlist items retrieved successfully.",
  "total": 1,
  "data": [
    {
      "wishlist_id": 4,
      "added_at": "2026-03-08T15:00:00.000000Z",
      "id": 101,
      "product_id": 101,
      "name": "NEXVIA Falcon EV Scooter",
      "slug": "nexvia-falcon-ev-scooter",
      "mrp": 89999.00,
      "booking_amount": 17999.80,
      "balance_amount": 71999.20,
      "in_stock": true,
      "main_image": "https://your-domain.com/uploads/products/falcon_main.jpg"
    }
  ]
}
```

---

### 6.2 Add Product to Wishlist
- **Method:** `POST`
- **URL:** `/api/customer/wishlist` or `/api/wishlist`
- **Request Body:**
```json
{
  "product_id": 101
}
```

#### Success Response (`201 Created`)
```json
{
  "status": true,
  "message": "Product added to wishlist successfully.",
  "is_in_wishlist": true,
  "data": {
    "wishlist_id": 5,
    "product_id": 101,
    "name": "NEXVIA Falcon EV Scooter"
  }
}
```

---

### 6.3 1-Click Toggle Wishlist Item
Toggles saved state: Adds if absent; Removes if present.

- **Method:** `POST`
- **URL:** `/api/customer/wishlist/toggle` or `/api/wishlist/toggle`
- **Request Body:**
```json
{
  "product_id": 101
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Product added to wishlist.",
  "is_in_wishlist": true,
  "product_id": 101,
  "total": 1
}
```

---

### 6.4 Check Product Status in Wishlist
- **Method:** `GET`
- **URL:** `/api/customer/wishlist/check/{productId}` or `/api/wishlist/check/{productId}`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "product_id": 101,
  "is_in_wishlist": true
}
```

---

### 6.5 Remove Item from Wishlist
- **Method:** `DELETE` or `POST`
- **URL:** `/api/customer/wishlist/{productId}` or `/api/wishlist/remove`
- **Request Body (if POST):** `{"product_id": 101}`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Product removed from wishlist successfully.",
  "is_in_wishlist": false,
  "product_id": 101,
  "total": 0
}
```

---

### 6.6 Clear Wishlist
- **Method:** `DELETE` or `POST`
- **URL:** `/api/customer/wishlist/clear` or `/api/wishlist/clear`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Wishlist cleared successfully. 1 item(s) removed.",
  "total": 0
}
```

---

## 7. Cart Management APIs

### 7.1 Get Cart Items & Financial Summary
Retrieves active items, item pricing, 20% token payable now, 80% balance due, and 18% GST statutory summary.

- **Method:** `GET` or `POST`
- **URL:** `/api/customer/cart` or `/api/cart`
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Cart items retrieved successfully.",
  "summary": {
    "unique_items": 1,
    "total_quantity": 1,
    "subtotal_mrp": 89999.00,
    "token_percentage": 20.0,
    "total_token_amount": 17999.80,
    "balance_percentage": 80.0,
    "total_balance_amount": 71999.20,
    "taxes": {
      "tax_rate_pct": 18.0,
      "taxable_amount": 76270.34,
      "cgst": 6864.33,
      "sgst": 6864.33,
      "total_tax": 13728.66
    },
    "delivery_fee": 0.0,
    "delivery_text": "FREE Delivery",
    "payable_now": 17999.80,
    "payable_later": 71999.20,
    "grand_total": 89999.00
  },
  "data": [
    {
      "cart_item_id": 2,
      "id": 2,
      "product_id": 101,
      "name": "NEXVIA Falcon EV Scooter",
      "slug": "nexvia-falcon-ev-scooter",
      "selected_color": "Midnight Black",
      "quantity": 1,
      "unit_mrp": 89999.00,
      "item_total_mrp": 89999.00,
      "token_percentage": 20.0,
      "token_amount": 17999.80,
      "balance_percentage": 80.0,
      "balance_amount": 71999.20,
      "main_image": "https://your-domain.com/uploads/products/falcon_main.jpg",
      "in_stock": true
    }
  ]
}
```

---

### 7.2 Add Item to Cart
- **Method:** `POST`
- **URL:** `/api/customer/cart` or `/api/cart/add` or `/api/cart`
- **Request Body:**
```json
{
  "product_id": 101,
  "quantity": 1,
  "selected_color": "Crimson Red"
}
```

#### Success Response (`201 Created`)
```json
{
  "status": true,
  "message": "Product added to cart successfully.",
  "item": {
    "cart_item_id": 3,
    "product_id": 101,
    "selected_color": "Crimson Red",
    "quantity": 1,
    "item_total_mrp": 89999.00,
    "token_amount": 17999.80,
    "balance_amount": 71999.20
  },
  "summary": { ... }
}
```

---

### 7.3 Update Cart Item Quantity / Color
- **Method:** `PUT` or `POST`
- **URL:** `/api/customer/cart/update` or `/api/cart/update`
- **Request Body:**
```json
{
  "cart_item_id": 3,
  "quantity": 2,
  "selected_color": "Metallic Silver"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Cart item updated successfully.",
  "item": {
    "cart_item_id": 3,
    "quantity": 2,
    "item_total_mrp": 179998.00,
    "token_amount": 35999.60,
    "balance_amount": 143998.40
  },
  "summary": { ... }
}
```

---

### 7.4 Remove Item from Cart
- **Method:** `DELETE` or `POST`
- **URL:** `/api/customer/cart/remove` or `/api/cart/{id}` or `/api/cart/remove`
- **Request Body (if POST):** `{"cart_item_id": 3}`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Item removed from cart successfully.",
  "deleted_count": 1,
  "summary": { ... }
}
```

---

### 7.5 Clear Cart
- **Method:** `DELETE` or `POST`
- **URL:** `/api/customer/cart/clear` or `/api/cart/clear`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Cart cleared successfully. 1 item(s) removed.",
  "summary": {
    "unique_items": 0,
    "total_quantity": 0,
    "grand_total": 0.0
  },
  "data": []
}
```

---

## 8. Checkout Calculation & Gateway Payment APIs

### 8.1 Compute Checkout & Tax Breakdown
Calculate token advance, remaining balance, GST schedule, and delivery options for a single item, multiple items, or the active cart.

- **Method:** `POST`
- **URL:** `/api/customer/checkout/calculate` or `/api/checkout/calculate`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body Options:
**Option A — Direct Product:**
```json
{
  "productId": 101,
  "selectedColor": "Midnight Black",
  "quantity": 1
}
```

**Option B — Multi-Item Array:**
```json
{
  "items": [
    { "productId": 101, "selectedColor": "Red", "quantity": 1 },
    { "productId": 102, "selectedColor": "Blue", "quantity": 1 }
  ]
}
```

**Option C — Empty payload (Calculates from user's active cart items).**

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Checkout amounts calculated successfully.",
  "calculation": {
    "summary": {
      "total_items_count": 1,
      "total_quantity": 1,
      "subtotal_mrp": 89999.00,
      "token_percentage": 20.0,
      "token_amount": 17999.80,
      "balance_percentage": 80.0,
      "balance_amount": 71999.20,
      "delivery_fee": 0.0,
      "grand_total": 89999.00
    },
    "taxes": {
      "tax_rate_percentage": 18.0,
      "taxable_amount": 76270.34,
      "cgst_percentage": 9.0,
      "cgst_amount": 6864.33,
      "sgst_percentage": 9.0,
      "sgst_amount": 6864.33,
      "total_tax": 13728.66,
      "tax_status": "Inclusive in MRP"
    },
    "delivery": {
      "charges": 0.0,
      "status": "FREE",
      "title": "Free Doorstep Delivery",
      "estimated_days": "5-7 Business Days",
      "includes": "Unboxing, inspection, and door delivery included."
    },
    "payment_schedule": {
      "token_20_percent": {
        "title": "Token Booking Amount (20%)",
        "percentage": "20%",
        "amount": 17999.80,
        "due": "Payable Now to Reserve Vehicle/Product",
        "timing": "Immediate at Checkout",
        "is_refundable": false
      },
      "balance_80_percent": {
        "title": "Remaining Balance (80%)",
        "percentage": "80%",
        "amount": 71999.20,
        "due": "Payable within 60 days before delivery",
        "timing": "Flexible within 60 Days",
        "eligible_for_transfer": true
      }
    },
    "payable_breakdown": {
      "payable_now": 17999.80,
      "payable_later": 71999.20,
      "total_payable": 89999.00
    },
    "items": [
      {
        "product_id": 101,
        "name": "NEXVIA Falcon EV Scooter",
        "selected_color": "Midnight Black",
        "quantity": 1,
        "unit_mrp": 89999.00,
        "item_total_mrp": 89999.00,
        "token_amount": 17999.80,
        "balance_amount": 71999.20
      }
    ]
  }
}
```

---

### 8.2 Create Gateway Payment Order (Razorpay)
Initializes a payment order on the gateway for the 20% advance token or full amount.

- **Method:** `POST`
- **URL:** `/api/customer/payments/create-order` or `/api/payments/create-order`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "productId": 101,
  "amountPayable": 17999.80,
  "currency": "INR",
  "selectedColor": "Midnight Black",
  "quantity": 1,
  "paymentType": "booking_20"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Payment order ID generated successfully.",
  "gateway": {
    "provider": "razorpay",
    "integration_mode": "mock_simulation",
    "is_live": false,
    "key_id": "rzp_test_nexvia_mock_key"
  },
  "order": {
    "id": "order_H8xZ9kLmPqRsTu",
    "order_id": "order_H8xZ9kLmPqRsTu",
    "entity": "order",
    "amount": 1799980,
    "amount_in_rupees": 17999.80,
    "currency": "INR",
    "receipt": "rcpt_20260310100000_123",
    "status": "created",
    "payment_type": "booking_20",
    "notes": {
      "product_id": 101,
      "product_name": "NEXVIA Falcon EV Scooter",
      "selected_color": "Midnight Black",
      "quantity": 1,
      "user_id": 12,
      "customer_name": "Rahul Sharma"
    }
  },
  "checkout_options": {
    "key": "rzp_test_nexvia_mock_key",
    "amount": 1799980,
    "currency": "INR",
    "name": "NEXVIA Mobility",
    "description": "Token Booking (20%) for NEXVIA Falcon EV Scooter",
    "order_id": "order_H8xZ9kLmPqRsTu",
    "prefill": {
      "name": "Rahul Sharma",
      "email": "rahul.sharma@example.com",
      "contact": "9876543210"
    },
    "theme": {
      "color": "#0D6EFD"
    }
  }
}
```

---

### 8.3 Verify Payment Callback
- **Method:** `POST`
- **URL:** `/api/customer/payments/verify` or `/api/payments/verify`
- **Request Body:**
```json
{
  "razorpay_order_id": "order_H8xZ9kLmPqRsTu",
  "razorpay_payment_id": "pay_9876543210abcdef"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Payment verified successfully.",
  "payment_status": "captured",
  "data": {
    "order_id": "order_H8xZ9kLmPqRsTu",
    "payment_id": "pay_9876543210abcdef",
    "status": "paid",
    "verified_at": "2026-03-10T10:05:00.000000Z"
  }
}
```

---

## 9. Flexi-Bookings & 60-Day Balance Engine APIs

### 9.1 List User Bookings
Fetch booking orders with status filtering (`ALL`, `ACTIVE`, `TRANSFERRED`, `COMPLETED`, `CANCELLED`).

- **Method:** `GET` or `POST`
- **URL:** `/api/customer/bookings` or `/api/bookings` or `/api/customer/bookings/list`
- **Headers:** `Authorization: Bearer <token>`
- **Query / Body Parameters:** `?status=ACTIVE`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Bookings retrieved successfully.",
  "filter": "ACTIVE",
  "total": 1,
  "counts": {
    "active": 1,
    "transferred": 0,
    "total": 1
  },
  "data": [
    {
      "id": 15,
      "booking_number": "NEX-2026-781923",
      "product_id": 101,
      "product_name": "NEXVIA Falcon EV Scooter",
      "model_code": "NEX-FLC-01",
      "selected_color": "Midnight Black",
      "quantity": 1,
      "imageUrl": "https://your-domain.com/uploads/products/falcon_main.jpg",
      "mrp": 89999.00,
      "booking_amount": 17999.80,
      "token_amount": 17999.80,
      "balance_amount": 71999.20,
      "booking_date": "2026-03-10",
      "balance_due_date": "2026-05-09",
      "days_remaining": 60,
      "is_overdue": false,
      "status_badge": "ACTIVE",
      "payment_type": "booking_20",
      "payment_status": "paid",
      "booking_status": "booked",
      "transfer_status": "original",
      "transfer_eligible": true,
      "non_refundable_accepted": true,
      "customer_name": "Rahul Sharma",
      "customer_phone": "9876543210",
      "shipping_address": "Flat 402, Green Avenue, FC Road",
      "qr_code_hash": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"
    }
  ]
}
```

---

### 9.2 Create Confirmed 20% Flexi-Booking
Creates a confirmed booking record upon successful 20% advance token payment. Automatically activates Self-Dealer eligibility if the product qualifies.

- **Method:** `POST`
- **URL:** `/api/customer/bookings` or `/api/bookings`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "productId": 101,
  "selectedColor": "Midnight Black",
  "quantity": 1,
  "totalMRP": 89999.00,
  "tokenAmount": 17999.80,
  "payment_type": "booking_20",
  "non_refundable_accepted": true,
  "referral_code": "NEX12345",
  "address": {
    "address_line": "Flat 402, Green Avenue, FC Road",
    "city": "Pune",
    "state": "Maharashtra",
    "pincode": "411001"
  },
  "customerDetails": {
    "name": "Rahul Sharma",
    "phone": "9876543210",
    "email": "rahul.sharma@example.com"
  }
}
```

#### Success Response (`201 Created`)
```json
{
  "status": true,
  "message": "New confirmed Flexi-Booking entry created successfully.",
  "booking": {
    "id": 15,
    "booking_number": "NEX-2026-781923",
    "product": {
      "id": 101,
      "name": "NEXVIA Falcon EV Scooter",
      "slug": "nexvia-falcon-ev-scooter",
      "model_code": "NEX-FLC-01",
      "selected_color": "Midnight Black",
      "quantity": 1,
      "imageUrl": "https://your-domain.com/uploads/products/falcon_main.jpg"
    },
    "financials": {
      "total_mrp": 89999.00,
      "token_amount_paid": 17999.80,
      "balance_amount_due": 71999.20,
      "token_percentage": 20.0,
      "balance_percentage": 80.0,
      "currency": "INR"
    },
    "timeline": {
      "booking_date": "2026-03-10",
      "balance_due_date": "2026-05-09",
      "days_remaining": 60,
      "due_in_days_text": "Payable within 60 days before delivery"
    },
    "customer": {
      "user_id": 12,
      "name": "Rahul Sharma",
      "phone": "9876543210",
      "email": "rahul.sharma@example.com"
    },
    "shipping_address": {
      "address": "Flat 402, Green Avenue, FC Road",
      "city": "Pune",
      "state": "Maharashtra",
      "pincode": "411001"
    },
    "status": {
      "booking_status": "booked",
      "payment_status": "paid",
      "transfer_status": "original",
      "transfer_eligible": true,
      "non_refundable": true
    },
    "qr_code_hash": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6",
    "created_at": "2026-03-10T10:15:00.000000Z"
  }
}
```

---

### 9.3 Get Booking Detail & Receipt
- **Method:** `GET`
- **URL:** `/api/customer/bookings/{id}` or `/api/bookings/{id}`
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "id": 15,
    "booking_number": "NEX-2026-781923",
    "customer_name": "Rahul Sharma",
    "customer_phone": "9876543210",
    "product_name": "NEXVIA Falcon EV Scooter",
    "model_code": "NEX-FLC-01",
    "mrp": 89999.00,
    "booking_amount": 17999.80,
    "balance_amount": 71999.20,
    "booking_date": "2026-03-10",
    "balance_due_date": "2026-05-09",
    "days_remaining": 60,
    "is_overdue": false,
    "payment_type": "booking_20",
    "payment_status": "paid",
    "booking_status": "booked",
    "transfer_status": "original",
    "shipping_address": "Flat 402, Green Avenue, FC Road",
    "city": "Pune",
    "state": "Maharashtra",
    "pincode": "411001",
    "qr_code_hash": "a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6"
  }
}
```

---

### 9.4 Pay Remaining 80% Balance (Cash / Wallet)
Clear the 80% remaining balance using standard cash gateway or NEXVIA Product Credit Wallet points.

- **Method:** `POST`
- **URL:** `/api/customer/bookings/{id}/pay-balance`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "use_product_credit": true
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Balance payment completed successfully.",
  "data": {
    "booking_number": "NEX-2026-781923",
    "credit_applied": 15000.00,
    "cash_paid": 56999.20,
    "balance_amount": 0.00,
    "payment_status": "fully_paid",
    "wallet_balance_remain": 0.00
  }
}
```

---

### 9.5 Initiate Booking Transfer
Initiate transfer of a 20% booking reservation to another customer. Sends an OTP to the initiator.

- **Method:** `POST`
- **URL:** `/api/customer/bookings/{id}/transfer`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "to_name": "Vikram Patel",
  "to_phone": "9812345678"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Transfer OTP generated. Verify OTP to complete transfer.",
  "transfer_id": 8,
  "otp_debug": null
}
```

---

### 9.6 Confirm Booking Transfer via OTP
Submits verified transfer request for Admin audit and approval.

- **Method:** `POST`
- **URL:** `/api/customer/bookings/{id}/transfer/confirm`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "transfer_id": 8,
  "otp": "654321"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "OTP verified successfully. Your booking transfer request has been submitted and is pending Admin Approval.",
  "data": {
    "booking_number": "NEX-2026-781923",
    "recipient_name": "Vikram Patel",
    "recipient_phone": "9812345678",
    "transfer_status": "pending_admin_approval"
  }
}
```

---

## 10. Multi-Item Orders & Delivery Tracking APIs

### 10.1 Multi-Item Order Checkout
Place combined cart checkout order with optional wallet credit deduction and automatic 7-stage delivery tracking assignment.

- **Method:** `POST`
- **URL:** `/api/customer/orders/checkout`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "items": [
    { "product_id": 101, "quantity": 1 }
  ],
  "shipping_address": "Flat 402, Green Avenue, FC Road",
  "city": "Pune",
  "state": "Maharashtra",
  "pincode": "411001",
  "payment_type": "booking_20",
  "payment_method": "upi",
  "use_product_credit": true
}
```

#### Success Response (`201 Created`)
```json
{
  "status": true,
  "message": "Order placed successfully.",
  "data": {
    "order_number": "NEX-ORD-20260310-8472",
    "total_amount": 89999.00,
    "booking_amount": 17999.80,
    "balance_amount": 71999.20,
    "product_credit_applied": 2500.00,
    "payment_type": "booking_20",
    "payment_status": "paid",
    "tracking_number": "TRK-49182740",
    "delivery_stage": "order_confirmed"
  }
}
```

---

### 10.2 Track 7-Stage Order Delivery
Track live delivery progress across the 7 milestones.

- **Method:** `GET`
- **URL:** `/api/deliveries/{trackingNumber}` or `/api/customer/deliveries/{trackingNumber}`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "tracking_number": "TRK-49182740",
    "current_stage": "dispatched",
    "current_stage_label": "Dispatched",
    "dispatched_at": "2026-03-10 14:30:00",
    "delivered_at": null,
    "installation_completed_at": null,
    "stages_flow": {
      "order_confirmed": "Order Confirmed",
      "processing": "Processing",
      "dispatched": "Dispatched",
      "out_for_delivery": "Out for Delivery",
      "delivered": "Delivered",
      "installation_pending": "Installation Pending",
      "installation_completed": "Installation Completed"
    }
  }
}
```

---

## 11. Warranty & Service Support APIs

### 11.1 List Customer Warranties
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
      "product_id": 101,
      "product_name": "NEXVIA Falcon EV Scooter",
      "model_code": "NEX-FLC-01",
      "serial_number": "SN-EV-2026-89123",
      "purchase_date": "2026-03-10",
      "warranty_start": "2026-03-10",
      "warranty_end": "2029-03-10",
      "status": "active",
      "document_url": "https://your-domain.com/warranties/cert_89123.pdf",
      "action": "RAISE SERVICE REQUEST"
    }
  ]
}
```

---

### 11.2 Create Service / Repair Ticket
Raise warranty claim, repair request, or technical support ticket with photo/video proof.

- **Method:** `POST`
- **URL:** `/api/customer/service-tickets`
- **Headers:** `Authorization: Bearer <token>`
- **Content-Type:** `multipart/form-data`

#### Request Body (Multipart)
```
subject: Brake sensor check required
service_type: repair (warranty, installation, repair, replacement, technical_support, complaint)
booking_id: 15
details: Front brake regenerative trigger is responding slowly after 500 km ride.
photo: [FILE ATTACHMENT: brake_photo.jpg]
```

#### Success Response (`201 Created`)
```json
{
  "status": true,
  "message": "Service ticket submitted successfully.",
  "data": {
    "id": 4,
    "ticket_number": "TKT-2026-92841",
    "subject": "Brake sensor check required",
    "service_type": "repair",
    "status": "open",
    "created_at": "2026-03-10 16:45:00"
  }
}
```

---

### 11.3 List Customer Service Tickets
- **Method:** `GET`
- **URL:** `/api/customer/service-tickets`
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": [
    {
      "id": 4,
      "ticket_number": "TKT-2026-92841",
      "subject": "Brake sensor check required",
      "service_type": "repair",
      "status": "open",
      "details": "Front brake regenerative trigger is responding slowly after 500 km ride.",
      "created_at": "2026-03-10 16:45:00"
    }
  ]
}
```

---

### 11.4 Schedule / Rate Installation
- **Method:** `POST`
- **URL:** `/api/customer/installations/schedule`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "booking_id": 15,
  "scheduled_at": "2026-03-15 11:00:00",
  "rating": 5,
  "feedback": "Punctual technician and thorough demo!"
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Installation schedule updated.",
  "data": {
    "id": 2,
    "technician_name": "Ramesh Verma",
    "technician_phone": "+91-9876543210",
    "scheduled_at": "2026-03-15 11:00:00",
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
- **Method:** `GET`
- **URL:** `/api/v1/self-dealer/status` or `/api/customer/self-dealer/status`
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "is_self_dealer": true,
    "self_dealer_status": "active",
    "self_dealer_code": "SD-12-PUN",
    "referral_code": "NEX98B2A1",
    "referral_url": "https://your-domain.com/ref/NEX98B2A1",
    "activated_at": "2026-03-01T10:00:00.000000Z",
    "activation_booking_id": 15,
    "activation_product_name": "NEXVIA Falcon EV Scooter",
    "wallet": {
      "available_points": 4500.00,
      "pending_points": 1000.00,
      "redeemed_points": 15000.00,
      "total_earned": 20500.00
    },
    "rule_version": "Variant A (10% → 12% → 15% → 18% → 20% → Reset)"
  }
}
```

---

### 12.3 Self Dealer Category 5-Stage Progression (v1)
- **Method:** `GET`
- **URL:** `/api/v1/self-dealer/categories` or `/api/customer/self-dealer/categories`
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
      "current_stage": 2,
      "max_stages": 5,
      "current_incentive_pct": 12.00,
      "next_incentive_pct": 15.00,
      "cycle_number": 1,
      "referrals_in_cycle": 1,
      "total_referrals_all_time": 1,
      "stages_progress": [
        { "stage": 1, "percentage": 10.00, "is_current": false, "is_completed": true },
        { "stage": 2, "percentage": 12.00, "is_current": true, "is_completed": false },
        { "stage": 3, "percentage": 15.00, "is_current": false, "is_completed": false },
        { "stage": 4, "percentage": 18.00, "is_current": false, "is_completed": false },
        { "stage": 5, "percentage": 20.00, "is_current": false, "is_completed": false }
      ],
      "is_final_stage": false,
      "next_action_label": "Next Referral earns 12% (Stage 2/5)"
    }
  ]
}
```

---

### 12.4 Self Dealer Category Detail (v1)
- **Method:** `GET`
- **URL:** `/api/v1/self-dealer/category/{id}` or `/api/customer/self-dealer/category/{id}`
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "category_id": 1,
    "category_name": "Electric Vehicles",
    "category_code": "EV",
    "current_stage": 2,
    "current_incentive_pct": 12.00,
    "cycle_number": 1,
    "referrals_count": 1,
    "total_all_time": 1
  }
}
```

---

### 12.5 Self Dealer Incentive Points Wallet (v1)
- **Method:** `GET`
- **URL:** `/api/v1/self-dealer/wallet` or `/api/customer/self-dealer/wallet`
- **Headers:** `Authorization: Bearer <token>`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "wallet_title": "MY INCENTIVE POINTS",
    "available_incentive_points": 4500.00,
    "pending_incentive_points": 1000.00,
    "redeemed_incentive_points": 15000.00,
    "reversed_incentive_points": 0.00,
    "lifetime_earned_points": 20500.00,
    "redemption_rules": {
      "can_redeem_on_booking_balance": true,
      "can_withdraw_cash": false,
      "points_currency_ratio": "1 Point = ₹1.00 Value"
    }
  }
}
```

---

### 12.6 Self Dealer Wallet Transaction Ledger (v1)
- **Method:** `GET`
- **URL:** `/api/v1/self-dealer/wallet/transactions` or `/api/customer/self-dealer/wallet/transactions`
- **Headers:** `Authorization: Bearer <token>`
- **Query Parameters:** `?page=1&per_page=20`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1
  },
  "data": [
    {
      "id": 42,
      "transaction_type": "referral_booking",
      "amount": 8999.90,
      "type": "credit",
      "status": "available",
      "description": "Referral commission (10%) for booking NEX-2026-781923 by Kavita Shah",
      "booking_number": "NEX-2026-781923",
      "category_name": "Electric Vehicles",
      "referral_stage": 1,
      "incentive_percentage": 10.00,
      "cycle_number": 1,
      "created_at": "2026-03-05T14:00:00.000000Z"
    }
  ]
}
```

---

### 12.7 Self Dealer Referrals List (v1)
- **Method:** `GET`
- **URL:** `/api/v1/self-dealer/referrals` or `/api/customer/self-dealer/referrals`
- **Headers:** `Authorization: Bearer <token>`
- **Query Parameters:** `?page=1&per_page=20`

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1
  },
  "data": [
    {
      "referral_id": 14,
      "referee_name": "Kavita Shah",
      "referee_phone_masked": "98******10",
      "booking_number": "NEX-2026-781923",
      "category_name": "Electric Vehicles",
      "stage": 1,
      "cycle_number": 1,
      "benefit_percentage": 10.00,
      "product_value": 89999.00,
      "eligible_product_value": 89999.00,
      "credit_earned": 8999.90,
      "status": "qualified",
      "notes": "Stage 1 referral qualification",
      "date": "2026-03-05 14:00",
      "approved_at": "2026-03-05 14:05"
    }
  ]
}
```

---

### 12.8 Apply & Validate Referral Code at Checkout (v1)
Validate a referral code and check anti-fraud rules (self-referral prevention & active dealer verification).

- **Method:** `POST`
- **URL:** `/api/v1/referral/apply` or `/api/customer/referral/apply`
- **Headers (Optional):** `Authorization: Bearer <token>`

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

#### Anti-Fraud / Error Response (`422 Unprocessable Entity`)
```json
{
  "status": false,
  "message": "You cannot use your own referral code."
}
```

---

### 12.9 Product Referral Benefit Calculator (v1)
Public calculator displaying potential self-dealer activation points (20%) and stage 1 to 5 earning simulation for a product.

- **Method:** `GET`
- **URL:** `/api/v1/products/{id}/referral-benefit` (e.g., `/api/v1/products/101/referral-benefit`)

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "product_id": 101,
    "product_name": "NEXVIA Falcon EV Scooter",
    "mrp": 89999.00,
    "eligible_referral_value": 89999.00,
    "is_self_dealer_activator": true,
    "referral_eligible": true,
    "self_dealer_activation": {
      "activation_percentage": 20.00,
      "potential_points": 17999.80,
      "banner_text": "Book this product to activate Self Dealership and get ₹17,999.80 (20%) in points!"
    },
    "referral_stages": [
      { "stage": 1, "percentage": 10.00, "potential_points": 8999.90 },
      { "stage": 2, "percentage": 12.00, "potential_points": 10799.88 },
      { "stage": 3, "percentage": 15.00, "potential_points": 13499.85 },
      { "stage": 4, "percentage": 18.00, "potential_points": 16199.82 },
      { "stage": 5, "percentage": 20.00, "potential_points": 17999.80 }
    ]
  }
}
```

---

### 12.10 Redeem Incentive Points on Booking Balance (v1)
Redeem available wallet points towards paying down the remaining balance of an active booking.

- **Method:** `POST`
- **URL:** `/api/v1/self-dealer/redeem` or `/api/customer/self-dealer/redeem`
- **Headers:** `Authorization: Bearer <token>`

#### Request Body
```json
{
  "booking_id": 15,
  "points": 4500
}
```

#### Success Response (`200 OK`)
```json
{
  "status": true,
  "message": "Successfully redeemed 4500 points toward your booking.",
  "data": {
    "points_redeemed": 4500,
    "remaining_balance_due": 67499.20,
    "booking_payment_status": "paid",
    "remaining_wallet_points": 0.00
  }
}
```

---

## 13. Summary of Standard HTTP Status Codes

| Status Code | Meaning | Typical Usage |
|---|---|---|
| `200 OK` | Request succeeded | Data fetched, updated, or action completed |
| `201 Created` | Resource created | Account registered, booking created, item added to cart |
| `400 Bad Request` | Invalid payload or missing parameters | Missing refresh token or parameter mismatch |
| `401 Unauthorized` | Missing / invalid Bearer token | Protected customer endpoints |
| `403 Forbidden` | Access denied | Account inactive or deactivated |
| `404 Not Found` | Resource not found | Product, category, booking, or address not found |
| `422 Unprocessable` | Validation error | Validation failure (e.g. duplicate phone, self-referral) |
| `429 Too Many Requests` | Rate limit hit | OTP resend within cooldown window |
| `500 Server Error` | Internal exception | Unexpected server failure |
