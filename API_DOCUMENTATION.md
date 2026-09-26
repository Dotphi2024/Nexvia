# NEXVIA API Documentation

> **Base URL**: `http://127.0.0.1:8000` (Local) / `https://your-domain.com` (Production)  
> **API Version**: `v1` / `Current`  
> **Headers**: `Accept: application/json`, `Content-Type: application/json`  
> **Authentication**: Strictly via Authorization Token (`Authorization: Bearer <api_token>` header, or `token` / `api_token` query/body parameter). Using `user_id` / `id` for authentication or impersonation is strictly forbidden and will return HTTP 401.

---

## Table of Contents

1. [Authentication & Profile](#1-authentication--profile)
2. [Categories & Banners](#2-categories--banners)
3. [Products & Search](#3-products--search)
4. [Cart & Wishlist](#4-cart--wishlist)
5. [User Addresses](#5-user-addresses)
6. [Checkout & Online Payments](#6-checkout--online-payments)
7. [Flexi-Bookings, 60-Day Flexible Payments & Reallocation](#7-flexi-bookings-60-day-flexible-payments--reallocation)
8. [Referral & Product Credit Wallet (2-Part Rewards)](#8-referral--product-credit-wallet)
9. [Self-Dealer Ecosystem & 5-Stage Cycle](#9-self-dealer-ecosystem--5-stage-cycle)
10. [Order Delivery Tracking](#10-order-delivery-tracking)
11. [Warranties, Service Tickets & Installations](#11-warranties-service-tickets--installations)
12. [CMS Dynamic Pages, Policies & Points](#12-cms-dynamic-pages-policies--points)
13. [Authorised Delivery & Service Partner (DSP) Ecosystem](#13-authorised-delivery--service-partner-dsp-ecosystem)
14. [DLS Agro & Farm Equipment APIs](#14-dls-agro--farm-equipment-apis)
15. [DSP Service & Problem Requests Management (Attendance, Location Routing & Resolution)](#15-dsp-service--problem-requests-management-attendance-location-routing--resolution)

---

## 1. Authentication & Profile

### 1.1 Send Registration / Login OTP
* **Method**: `POST`
* **URL**: `/api/auth/send-otp` (or `/api/customer/send-otp`)
* **Auth**: Public

#### Request Body (For Registration):
```json
{
  "phone": "9876543210",
  "email": "rahul@example.com",
  "name": "Rahul Sharma",
  "type": "register"
}
```

#### Request Body (For Login):
```json
{
  "phone": "9876543210"
}
```
*(Or with email: `{"email": "rahul@example.com"}`)*

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "OTP sent successfully to your mobile and email.",
  "phone": "9876543210",
  "email": "rahul@example.com",
  "otp_debug": "123456"
}
```

---

### 1.2 Register Customer (Requires OTP)
* **Method**: `POST`
* **URL**: `/api/auth/register` (or `/api/customer/register`)
* **Auth**: Public

#### Request Body
```json
{
  "name": "Rahul Sharma",
  "phone": "9876543210",
  "email": "rahul@example.com",
  "password": "Password@123",
  "otp": "123456",
  "referral_code": "NEXAB12CD",
  "pincode": "411001",
  "city": "Pune",
  "state": "Maharashtra"
}
```

#### Response (`201 Created`)
```json
{
  "status": true,
  "message": "Registration successful.",
  "token": "a1b2c3d4e5f6...",
  "data": {
    "id": 42,
    "name": "Rahul Sharma",
    "phone": "9876543210",
    "email": "rahul@example.com",
    "referral_code": "NEX-987654",
    "referral_url": "http://127.0.0.1:8000/ref/NEX-987654",
    "is_self_dealer": false,
    "self_dealer_status": "inactive",
    "wallet_balance": 0.00
  }
}
```

---

### 1.3 Pure OTP Login (2-Step or Single-Step)

#### Step 1: Request OTP
* **Method**: `POST`
* **URL**: `/api/customer/login` (or `/api/auth/send-otp`)
* **Request Body**:
```json
{
  "phone": "9876543210"
}
```
* **Response (`200 OK`)**:
```json
{
  "status": true,
  "otp_sent": true,
  "message": "OTP sent to your registered mobile and email. Please enter the OTP to complete login.",
  "phone": "9876543210",
  "email": "rahul@example.com"
}
```

#### Step 2: Submit OTP to Complete Login
* **Method**: `POST`
* **URL**: `/api/customer/login` (or `/api/auth/verify-otp`)
* **Request Body**:
```json
{
  "phone": "9876543210",
  "otp": "123456"
}
```
* **Response (`200 OK`)**:
```json
{
  "status": true,
  "message": "Login successful",
  "token": "a1b2c3d4e5f6...",
  "data": {
    "id": 42,
    "name": "Rahul Sharma",
    "phone": "9876543210",
    "email": "rahul@example.com",
    "referral_code": "NEX-987654",
    "wallet_balance": 0.00,
    "status": "active"
  }
}
```

---

### 1.4 Resend OTP
* **Method**: `POST`
* **URL**: `/api/auth/resend-otp` (or `/api/customer/resend-otp`)
* **Request Body**:
```json
{
  "phone": "9876543210",
  "type": "register"
}
```
*(For login resend, omit `type` or pass `"type": "login"`)*

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "OTP resent to your WhatsApp and email.",
  "phone": "9876543210",
  "otp_debug": "123456"
}
```

---

### 1.5 Verify OTP (Standalone Alternative)
* **Method**: `POST`
* **URL**: `/api/auth/verify-otp` (or `/api/customer/verify-otp`)
* **Auth**: Public

#### Request Body
```json
{
  "phone": "9876543210",
  "otp": "123456"
}
```
*(Or with email: `{"email": "rahul@example.com", "otp": "123456"}`)*

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "OTP verified. Login successful!",
  "token": "a1b2c3d4e5f6...",
  "data": {
    "id": 42,
    "name": "Rahul Sharma",
    "phone": "9876543210",
    "email": "rahul@example.com",
    "referral_code": "NEX-987654",
    "wallet_balance": 0.00,
    "status": "active"
  }
}
```

---

### 1.6 Get Customer Profile
* **Method**: `GET`
* **URL**: `/api/user/profile` (or `/api/customer/profile`)
* **Auth**: Required (`customer.auth`)

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "id": 42,
    "name": "Rahul Sharma",
    "email": "rahul@example.com",
    "phone": "9876543210",
    "wallet_balance": 15000.00,
    "referral_code": "NEX-987654",
    "is_self_dealer": true,
    "self_dealer_status": "active"
  }
}
```

---

### 1.7 Update Profile
* **Method**: `PUT` or `POST`
* **URL**: `/api/user/profile` (or `/api/customer/update-profile`)
* **Auth**: Required (`customer.auth`)

#### Request Body
```json
{
  "name": "Rahul M. Sharma",
  "email": "rahul.sharma@example.com"
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Profile updated successfully.",
  "data": {
    "id": 42,
    "name": "Rahul M. Sharma",
    "email": "rahul.sharma@example.com"
  }
}
```

---

### 1.8 Customer Logout
* **Method**: `POST`
* **URL**: `/api/auth/logout` (or `/api/customer/logout`, `/api/logout`)
* **Auth**: Required (`customer.auth` or `Authorization: Bearer <api_token>`)
* **Headers**: `Authorization: Bearer <api_token>` (strictly required)

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "User logged out successfully.",
  "data": {
    "id": 42,
    "name": "Rahul Sharma",
    "phone": "9876543210"
  }
}
```

---

## 2. Categories & Banners

### 2.1 List All Categories
* **Method**: `GET`
* **URL**: `/api/categories` (or `/api/customer/categories`)
* **Auth**: Public

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "name": "Smart LED TV",
      "slug": "smart-led-tv",
      "type": "electronics",
      "category_code": "TV",
      "commission_percentage": 10.0,
      "reward_slab": "10% → 20% Product Credit",
      "stages_cycle": [10.0, 12.0, 15.0, 18.0, 20.0],
      "stages_info": [
        { "stage": 1, "reward_percentage": 10.0, "label": "Stage 1: 10% Product Credit" },
        { "stage": 2, "reward_percentage": 12.0, "label": "Stage 2: 12% Product Credit" },
        { "stage": 3, "reward_percentage": 15.0, "label": "Stage 3: 15% Product Credit" },
        { "stage": 4, "reward_percentage": 18.0, "label": "Stage 4: 18% Product Credit" },
        { "stage": 5, "reward_percentage": 20.0, "label": "Stage 5: 20% Product Credit" }
      ]
    }
  ]
}
```

> **Referral Reward Structure Breakdown**:
> - Stage-based incentives (`10% → 12% → 15% → 18% → 20%`) are defined in Referral Stage Config.
> - When a referred customer pays their 20% booking deposit, the referrer receives rewards calculated at their current eligible stage percentage on that deposit.
> - When the customer completes the remaining 80% balance (via EMI, lump-sum, or flexible payments), the **exact same eligible stage percentage** is applied to the 80% balance as well. There is no separate 80% referral percentage when adding or configuring categories.

---

### 2.2 Home Section Promotional Banners
* **Method**: `GET`
* **URL**: `/api/home/banners` (or `/api/banners`, `/api/home-banners`)
* **Auth**: Public

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "title": "Grand Launch Festival",
      "subtitle": "Book with just 20% down payment",
      "image_url": "http://127.0.0.1:8000/uploads/banners/banner_1.jpg",
      "target_url": "/products/nexvia-55-inch-ultra-hd-4k-smart-led-tv",
      "sort_order": 1
    }
  ]
}
```

---

## 3. Products & Search

### 3.1 List Products
* **Method**: `GET`
* **URL**: `/api/products` (or `/api/customer/products`)
* **Query Params**: `category_id`, `type`, `page`, `per_page`
* **Auth**: Public

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "name": "NEXVIA 55-inch Ultra HD 4K Smart LED TV",
      "slug": "nexvia-55-inch-ultra-hd-4k-smart-led-tv",
      "model_code": "NEX-TV55-4K",
      "sku": "SKU-TV-001",
      "mrp": 50000.0,
      "booking_percentage": 20.0,
      "token_amount": 10000.0,
      "balance_amount": 40000.0,
      "self_dealer_eligible": true,
      "main_image": "http://127.0.0.1:8000/uploads/products/tv.jpg",
      "status": "active"
    }
  ]
}
```

---

### 3.2 Product Detail
* **Method**: `GET`
* **URL**: `/api/products/{idOrSlug}` (or `/api/customer/products/{idOrSlug}`)
* **Auth**: Public

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Product full details & specifications retrieved successfully.",
  "data": {
    "id": 1,
    "name": "NEXVIA 55-inch Ultra HD 4K Smart LED TV",
    "slug": "nexvia-55-inch-ultra-hd-4k-smart-led-tv",
    "model_code": "NEX-TV55-4K",
    "sku": "SKU-TV-001",
    "overview": "Experience true immersion with NEXVIA 55-inch 4K Ultra HD Smart LED Display. Powered by Quantum Color Engine, Dolby Atmos Audio, and seamless dual-band Wi-Fi.",
    "description": "Experience true immersion with NEXVIA 55-inch 4K Ultra HD Smart LED Display. Powered by Quantum Color Engine, Dolby Atmos Audio, and seamless dual-band Wi-Fi.",
    "category": {
      "id": 1,
      "name": "Smart LED TV",
      "slug": "smart-led-tv",
      "referral_category_code": "TV"
    },
    "mrp": 50000.00,
    "booking_percentage": 20.00,
    "booking_amount": 10000.00,
    "balance_amount": 40000.00,
    "balance_due_days": 60,
    "pricing": {
      "mrp": 50000.00,
      "booking_percentage": 20.00,
      "booking_amount": 10000.00,
      "balance_amount": 40000.00,
      "balance_due_days": 60,
      "currency": "INR",
      "currency_symbol": "₹",
      "pricing_summary": "Pay ₹10,000.00 (20%) now, pay balance ₹40,000.00 within 60 days."
    },
    "referral_eligible": true,
    "self_dealer_eligible": true,
    "eligible_referral_value": 50000.00,
    "self_dealer_benefit": {
      "activates_self_dealer": true,
      "activation_points_pct": 20.00,
      "activation_points_value": 10000.00,
      "banner_title": "SELF DEALER BENEFIT",
      "banner_text": "Book this product and become an eligible Self Dealer. Earn ₹10,000.00 (20%) in activation points!"
    },
    "stock": 25,
    "in_stock": true,
    "stock_status": "In Stock",
    "main_image": "http://127.0.0.1:8000/uploads/products/tv.jpg",
    "imageUrl": "http://127.0.0.1:8000/uploads/products/tv.jpg",
    "gallery": [
      "http://127.0.0.1:8000/uploads/products/tv_angle1.jpg",
      "http://127.0.0.1:8000/uploads/products/tv_angle2.jpg"
    ],
    "video_url": "https://www.youtube.com/watch?v=example",
    "features": [
      "55-inch 4K Ultra HD IPS Panel (3840 x 2160)",
      "Dolby Vision HDR & Atmos 30W Soundbar Speakers",
      "Google TV with Voice Assistant & Hands-free Mic",
      "HDMI 2.1 eARC & Dual Band 2.4/5GHz Wi-Fi"
    ],
    "key_features": [
      "55-inch 4K Ultra HD IPS Panel (3840 x 2160)",
      "Dolby Vision HDR & Atmos 30W Soundbar Speakers",
      "Google TV with Voice Assistant & Hands-free Mic",
      "HDMI 2.1 eARC & Dual Band 2.4/5GHz Wi-Fi"
    ],
    "specs": {
      "Screen Size": "55 Inches",
      "Resolution": "4K Ultra HD (3840 x 2160)",
      "Refresh Rate": "60 Hz (120 Hz Motion Rate)",
      "Operating System": "Google TV Android 12",
      "Audio Output": "30W Dolby Atmos",
      "Connectivity": "3 HDMI 2.1, 2 USB, Bluetooth 5.2"
    },
    "specifications": [
      { "name": "Screen Size", "value": "55 Inches" },
      { "name": "Resolution", "value": "4K Ultra HD (3840 x 2160)" },
      { "name": "Refresh Rate", "value": "60 Hz (120 Hz Motion Rate)" },
      { "name": "Operating System", "value": "Google TV Android 12" },
      { "name": "Audio Output", "value": "30W Dolby Atmos" },
      { "name": "Connectivity", "value": "3 HDMI 2.1, 2 USB, Bluetooth 5.2" }
    ],
    "technical_specifications": [
      { "name": "Screen Size", "value": "55 Inches" },
      { "name": "Resolution", "value": "4K Ultra HD (3840 x 2160)" },
      { "name": "Refresh Rate", "value": "60 Hz (120 Hz Motion Rate)" },
      { "name": "Operating System", "value": "Google TV Android 12" },
      { "name": "Audio Output", "value": "30W Dolby Atmos" },
      { "name": "Connectivity", "value": "3 HDMI 2.1, 2 USB, Bluetooth 5.2" }
    ],
    "warranty_info": "3 Years Comprehensive Panel Warranty",
    "installation_info": "Free Wall Mount & Table-top Installation within 48 Hours",
    "delivery_info": "Delivered and Inspected by Local DSP",
    "is_featured": true,
    "status": "active"
  }
}
```

---

### 3.3 Search Products
* **Method**: `GET` / `POST`
* **URL**: `/api/products/search`
* **Query Params / Body**: `query=tv`
* **Auth**: Public

---

### 3.4 Trending Products
* **Method**: `GET`
* **URL**: `/api/products/trending`
* **Auth**: Public

---

## 4. Cart & Wishlist

### 4.1 Get Customer Cart
* **Method**: `GET`
* **URL**: `/api/customer/cart`
* **Auth**: Required (`customer.auth`)

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "items": [
      {
        "id": 10,
        "product_id": 1,
        "product_name": "NEXVIA 55-inch Ultra HD 4K Smart LED TV",
        "quantity": 1,
        "unit_mrp": 50000.00,
        "token_amount_each": 10000.00,
        "balance_due_each": 40000.00,
        "total_token": 10000.00
      }
    ],
    "cart_totals": {
      "total_mrp": 50000.00,
      "total_token_due_now": 10000.00,
      "total_balance_due_later": 40000.00
    }
  }
}
```

---

### 4.2 Add to Cart
* **Method**: `POST`
* **URL**: `/api/customer/cart/add`
* **Auth**: Required (`customer.auth`)

#### Request Body
```json
{
  "product_id": 1,
  "quantity": 1,
  "selected_color": "Midnight Black"
}
```

---

### 4.3 Update Cart Quantity
* **Method**: `PUT` or `POST`
* **URL**: `/api/customer/cart/{id}/quantity`
* **Auth**: Required (`customer.auth`)

#### Request Body
```json
{
  "quantity": 2
}
```

---

### 4.4 Remove Item from Cart
* **Method**: `DELETE` or `POST`
* **URL**: `/api/customer/cart/{id}` (or `/api/customer/cart/remove`)
* **Auth**: Required (`customer.auth`)

---

### 4.5 Wishlist APIs
* `GET /api/customer/wishlist` — View wishlist
* `POST /api/customer/wishlist/toggle` — Toggle product in/out of wishlist (`product_id`)
* `GET /api/customer/wishlist/check/{productId}` — Check if product is favorited
* `DELETE /api/customer/wishlist/clear` — Clear entire wishlist

---

## 5. User Addresses

### 5.1 List Saved Addresses
* **Method**: `GET`
* **URL**: `/api/user/addresses` (or `/api/addresses`, `/api/customer/addresses`)
* **Auth**: Required (`customer.auth`)

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": [
    {
      "id": 5,
      "name": "Home",
      "phone": "9876543210",
      "street": "Flat 402, Sunshine Heights, MG Road",
      "city": "Mumbai",
      "state": "Maharashtra",
      "pincode": "400001",
      "is_default": true
    }
  ]
}
```

---

### 5.2 Add New Address
* **Method**: `POST`
* **URL**: `/api/user/addresses` (or `/api/addresses`)
* **Auth**: Required (`customer.auth`)

#### Request Body
```json
{
  "name": "Office",
  "phone": "9876543210",
  "street": "Cyber City, Tower B, 5th Floor",
  "city": "Pune",
  "state": "Maharashtra",
  "pincode": "411028",
  "is_default": false
}
```

---

### 5.3 Delete Address
* **Method**: `DELETE` or `POST`
* **URL**: `/api/user/addresses/{id}` (or `/api/user/addresses/delete`)
* **Auth**: Required (`customer.auth`)

---

## 6. Checkout & Online Payments

### 6.1 Multi-Item Checkout Calculation
* **Method**: `POST`
* **URL**: `/api/customer/checkout/calculate`
* **Auth**: Required (`customer.auth`)

#### Request Body
```json
{
  "items": [
    { "product_id": 1, "quantity": 1 }
  ],
  "pincode": "400001",
  "use_product_credit": true
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "total_mrp": 50000.0,
    "token_amount_due_now": 10000.0,
    "balance_amount_due_60days": 40000.0,
    "product_credit_available": 15000.0,
    "product_credit_applied": 10000.0,
    "cash_payable_now": 0.0,
    "cgst": 900.0,
    "sgst": 900.0
  }
}
```

---

### 6.2 Online Payment Order Creation (Razorpay / Cashfree)
* **Method**: `POST`
* **URL**: `/api/payments/create-order`
* **Auth**: Required (`customer.auth`)

#### Request Body
```json
{
  "amount": 10000.00,
  "currency": "INR",
  "booking_number": "BK-20260916-1234"
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "order_id": "order_ABC123XYZ",
    "amount": 1000000,
    "currency": "INR",
    "key": "rzp_test_xxxxxxx"
  }
}
```

---

### 6.3 Verify Online Payment
* **Method**: `POST`
* **URL**: `/api/payments/verify`
* **Auth**: Required (`customer.auth`)

#### Request Body
```json
{
  "razorpay_order_id": "order_ABC123XYZ",
  "razorpay_payment_id": "pay_XYZ987654",
  "razorpay_signature": "signature_hash..."
}
```

---

## 7. Flexi-Bookings, 60-Day Flexible Payments & Reallocation

### 7.1 List Customer Bookings
* **Method**: `GET` or `POST`
* **URL**: `/api/customer/bookings` (or `/api/bookings`, `/api/customer/bookings/list`)
* **Auth**: Required (`customer.auth`)

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": [
    {
      "id": 88,
      "booking_number": "BK-20260910-4491",
      "product_name": "NEXVIA 55-inch Ultra HD 4K Smart LED TV",
      "mrp": 50000.0,
      "booking_amount_paid": 10000.0,
      "balance_amount_due": 40000.0,
      "filled_amount": 10000.0,
      "booking_date": "2026-09-10",
      "balance_due_date": "2026-11-09",
      "payment_status": "partially_paid",
      "booking_status": "confirmed",
      "days_remaining": 54,
      "is_expired_60_days": false,
      "can_reallocate_paid_amount": false,
      "cancellation_allowed": false
    }
  ]
}
```

---

### 7.2 Create New Flexi-Booking (Token Down Payment: 20%)
* **Method**: `POST`
* **URL**: `/api/customer/bookings` (or `/api/bookings`, `/api/booking`)
* **Auth**: Required (`customer.auth`)

> **Strict Commitment Policy**: Once the 20% booking deposit is confirmed, the booking is **strictly non-cancellable and non-refundable**.
>
> **Referral Part 1 Reward**: The referrer instantly receives their **Part 1 Referral Stage Reward** (10% to 20% based on their active stage) computed on the 20% deposit amount.

#### Request Body
```json
{
  "product_id": 1,
  "selected_color": "Midnight Black",
  "quantity": 1,
  "shipping_address": "Flat 402, Sunshine Heights, Mumbai",
  "city": "Mumbai",
  "state": "Maharashtra",
  "pincode": "400001",
  "referral_code": "NEXAB12CD"
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "New confirmed Flexi-Booking entry created successfully.",
  "booking": {
    "id": 88,
    "booking_number": "BK-20260916-7788",
    "financials": {
      "total_mrp": 50000.0,
      "token_amount_paid": 10000.0,
      "balance_amount_due": 40000.0,
      "token_percentage": 20.0,
      "balance_percentage": 80.0
    },
    "cancellation_allowed": false,
    "days_remaining": 60
  }
}
```

---

### 7.3 Flexible 60-Day Balance Payments (Full, EMI, or Flexible Any Amount)
* **Method**: `POST`
* **URL**: `/api/customer/bookings/{id}/pay-balance`
* **Auth**: Required (`customer.auth`)

> **Payment Modes**:
> 1. `payment_mode: "full"` — Clears entire remaining balance at once.
> 2. `payment_mode: "emi"` — Pays the calculated monthly EMI installment (`emi_tenure`: 3, 6, 9, or 12 months).
> 3. `payment_mode: "flexible"` — Allows customer to pay **any custom amount** (minimum ₹100, up to current balance) at any time within the 60 days until the balance reaches ₹0.
>
> **Referral Balance Completion Reward**: When the remaining balance reaches ₹0 (`payment_status: "fully_paid"`), the system automatically triggers `award80PercentCategoryCompletionCredit()`, applying the **exact same eligible referral percentage** (from the referrer's stage when the 20% booking deposit was made) to the 80% balance amount and crediting the referrer's wallet!

#### Request Body (Flexible Custom Amount Example)
```json
{
  "payment_mode": "flexible",
  "custom_amount": 5000.00,
  "reference_no": "UPI-UTR-9876543210",
  "use_product_credit": false
}
```

#### Request Body (EMI Mode Example)
```json
{
  "payment_mode": "emi",
  "emi_tenure": 6,
  "reference_no": "UPI-UTR-9876543210"
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Payment of ₹5,000.00 recorded successfully towards balance. Remaining balance: ₹35,000.00.",
  "data": {
    "booking_number": "BK-20260916-7788",
    "amount_paid_now": 5000.0,
    "balance_amount": 35000.0,
    "filled_amount": 15000.0,
    "payment_status": "partially_paid",
    "payment_mode": "flexible",
    "days_remaining": 48
  }
}
```

---

### 7.4 Strict Non-Cancellable Policy Enforcement
* **Method**: `POST`
* **URL**: `/api/customer/bookings/{id}/cancel` (or `/api/bookings/{id}/cancel`, `/api/booking/{id}/cancel`)
* **Auth**: Required (`customer.auth`)

> **Policy Rule**: In accordance with platform terms, once the 20% booking deposit is confirmed, bookings are strictly non-cancellable and non-refundable. Referral stage credits are locked upon booking. If the remaining balance is not paid within 60 days, the customer can reallocate their paid amount to purchase another item via Section 7.6.

#### Response (`422 Unprocessable Entity`)
```json
{
  "status": false,
  "message": "Cancellation is not permitted. Once the 20% booking deposit is confirmed, bookings are strictly non-cancellable and non-refundable."
}
```

---

### 7.5 Transfer Booking Ownership
* `POST /api/customer/bookings/{id}/transfer` — Request transfer (`to_name`, `to_phone`)
* `POST /api/customer/bookings/{id}/transfer/confirm` — Confirm OTP for transfer

---

### 7.6 Post-60-Day Balance Reallocation ("Buy Another Item with Paid Amount")
* **Method**: `POST`
* **URL**: `/api/customer/bookings/{id}/reallocate`
* **Aliases**: `/api/bookings/{id}/reallocate`, `/api/booking/{id}/reallocate`, `/booking/reallocate/{bookingNumber}` (Web)
* **Auth**: Required (`customer.auth`)

> **Eligibility Criteria**:
> - `is_expired_60_days` must be `true` (`days_remaining <= 0` or `balance_due_date` is past).
> - `payment_status` must NOT be `fully_paid`.
> - `booking_status` must NOT already be `reallocated`.
>
> **Action**: Converts the total filled amount (`mrp - balance_amount`, including 20% initial deposit + all partial payments made) into NEXVIA Product Credits credited directly to the customer's wallet. Marks booking as `reallocated`. The customer can use this wallet balance to purchase any other item from the catalog.

#### Request Body
```json
{}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "₹15,000.00 from booking #BK-20260916-7788 has been reallocated to your Product Credits wallet. You can now use it to purchase another catalog product.",
  "data": {
    "booking_id": 88,
    "booking_number": "BK-20260916-7788",
    "reallocated_amount": 15000.0,
    "new_wallet_balance": 15000.0,
    "booking_status": "reallocated",
    "product_catalog_url": "/products"
  }
}
```

---

### 7.7 Get Single Booking Details
* **Method**: `GET`
* **URL**: `/api/customer/bookings/{id}` (or `/api/bookings/{id}`)
* **Auth**: Required (`customer.auth`)

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "id": 88,
    "booking_number": "BK-20260916-7788",
    "product_name": "NEXVIA 55-inch Ultra HD 4K Smart LED TV",
    "model_code": "NEX-TV55-4K",
    "mrp": 50000.0,
    "booking_amount": 10000.0,
    "balance_amount": 40000.0,
    "filled_amount": 10000.0,
    "booking_date": "2026-09-16",
    "balance_due_date": "2026-11-15",
    "days_remaining": 55,
    "is_expired_60_days": false,
    "can_reallocate_paid_amount": false,
    "cancellation_allowed": false,
    "payment_status": "partially_paid",
    "booking_status": "confirmed",
    "balance_payment_mode": "flexible",
    "balance_payments_history": [
      {
        "amount": 10000.0,
        "mode": "booking_deposit",
        "reference_no": "UPI-DEP-12345",
        "paid_at": "2026-09-16 10:00:00"
      }
    ],
    "dsp": {
      "id": 5,
      "business_name": "Apex Mobility DSP Hub",
      "mobile": "9876500000"
    }
  }
}
```

---

### 7.8 Select Delivery DSP Partner for Booking
* **Method**: `POST`
* **URL**: `/api/customer/bookings/{id}/select-dsp`
* **Aliases**: `/api/bookings/{id}/select-dsp`, `/api/booking/{id}/select-dsp`, `/booking/select-dsp/{bookingNumber}` (Web)
* **Auth**: Required (`customer.auth`)

> Allows the customer to select their preferred Authorised DSP Partner (loaded from the nearby DSP API) to coordinate the doorstep delivery, unboxing, and certified installation.

#### Request Body
```json
{
  "dsp_id": 12
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "DSP Partner 'Patil Logistics & Electric Mobility Hub' assigned to booking #BK-20260916-7788. Delivery and unboxing will be coordinated by this partner.",
  "booking_number": "BK-20260916-7788",
  "assigned_dsp": {
    "id": 12,
    "business_name": "Patil Logistics & Electric Mobility Hub",
    "mobile": "9876501234",
    "territory": "Pune Central & PCMC",
    "address": "Shop 12, Market Yard, Pune",
    "pincode": "411001",
    "district": "Pune",
    "state": "Maharashtra"
  }
}
```

---

## 8. Referral & Product Credit Wallet

### 8.1 Primary Referral Tracking Endpoint (New & Enhanced)
* **Method**: `GET` / `POST`
* **URL**: `/api/customer/referrals`
* **Aliases**: `/api/referrals`, `/api/customer/my-referrals`, `/api/customer/referrals/list`
* **Auth**: Required (`customer.auth`)
* **Query Parameters**:
  * `status`: `all`, `successful` / `available`, `pending`, `cancelled` / `reversed`
  * `type`: `all`, `referral`, `activation`
  * `per_page`: Default `20` (Max: `100`)
  * `page`: Default `1`

#### Response (`200 OK`)
```json
{
  "status": true,
  "summary": {
    "total_referrals_count": 5,
    "successful_referrals_count": 3,
    "pending_referrals_count": 1,
    "cancelled_referrals_count": 1,
    "total_credited_points_received": 15000.00,
    "total_pending_points": 5000.00,
    "total_cancelled_points": 5000.00,
    "wallet_available_balance": 15000.00,
    "wallet_pending_balance": 5000.00,
    "current_stage": 2,
    "current_stage_percentage": 12.00,
    "current_stage_label": "Stage 2 (12%)",
    "current_cycle_number": 1,
    "next_stage_percentage": 15.00,
    "referral_code": "NEX-AB12CD",
    "referral_url": "http://127.0.0.1:8000/ref/NEX-AB12CD"
  },
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 5
  },
  "data": [
    {
      "id": 101,
      "referral_id": 101,
      "transaction_type": "referral",
      "transaction_type_label": "Referral Reward",
      "referee": {
        "id": 42,
        "name": "Rahul Sharma",
        "phone_masked": "98******21",
        "joined_at": "2026-09-10 14:30:00"
      },
      "booking": {
        "id": 88,
        "booking_number": "BK-20260910-4491",
        "product_id": 1,
        "product_name": "NEXVIA 55-inch Ultra HD 4K Smart LED TV",
        "category_name": "Smart LED TV",
        "mrp": 50000.00,
        "eligible_value": 50000.00,
        "booking_amount_paid": 10000.00,
        "balance_amount": 40000.00,
        "payment_status": "fully_paid",
        "booking_status": "delivered",
        "is_purchase_complete": true
      },
      "percentage": 10.00,
      "percentage_label": "10%",
      "stage": 1,
      "stage_label": "Stage 1 (10%)",
      "cycle_number": 1,

      "status": "available",
      "is_successful": true,
      "reward_received": true,
      "is_pending": false,
      "is_cancelled": false,

      "credit_points": 5000.00,
      "credited_points_received": 5000.00,
      "pending_points": 0.00,
      "cancelled_points": 0.00,

      "status_badge": "Credit Received",
      "status_description": "10% reward (₹5,000.00) successfully credited to wallet.",
      "approved_at": "2026-09-12 18:00:00",
      "reversed_at": null,
      "cancellation_reason": null,
      "notes": "Stage 1 | Cycle 1 | 10% of ₹50000",
      "created_at": "2026-09-10 14:35:00"
    }
  ]
}
```

---

### 8.2 Customer Referral Dashboard
* **Method**: `GET`
* **URL**: `/api/customer/referral-dashboard`
* **Auth**: Required (`customer.auth`)

---

### 8.3 Two-Part Referral Reward Structure Breakdown

NEXVIA implements a structured two-part referral reward policy on all product bookings:

| Reward Component | Trigger Event | Calculation Base | Commission Rate | Recipient Ledger Type |
| :--- | :--- | :--- | :--- | :--- |
| **Part 1: Deposit Stage Reward** | Instant upon payment of 20% booking deposit | 20% Deposit Amount (`booking_amount`) | Referrer's active Stage Tier: **10% &rarr; 12% &rarr; 15% &rarr; 18% &rarr; 20%** | `transaction_type: referral` |
| **Part 2: Category Completion Reward** | When 80% balance is fully settled (via EMI, full, or flexible payments) | 80% Balance Amount (`mrp - booking_amount`) | Category Commission %: **e.g. 5.00%** (configured per category) | `transaction_type: category_completion` |

> **Key Enforcement Details**:
> 1. **No Cancellation**: Once Part 1 is awarded on deposit confirmation, bookings are strictly non-cancellable.
> 2. **Flexible Payments**: Customers can pay any custom amount towards the 80% balance over the 60-day window.
> 3. **Automatic Reward**: When the remaining balance reaches ₹0 (`fully_paid`), Part 2 Category Reward is automatically credited to the referrer's Product Credit wallet without requiring manual intervention.
> 4. **Anti-Duplication**: Category completion rewards are guarded by idempotency keys to ensure they are credited exactly once per booking.

---

## 9. Self-Dealer Ecosystem & 5-Stage Cycle

### 9.1 Self-Dealer Status & 20% Stake Policy
* **Method**: `GET`
* **URL**: `/api/customer/self-dealer/status` (or `/api/v1/self-dealer/status`)
* **Auth**: Required (`customer.auth`)

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "is_self_dealer": true,
    "self_dealer_status": "active",
    "self_dealer_code": "NEX-001234",
    "referral_code": "NEXAB12CD",
    "referral_url": "http://127.0.0.1:8000/ref/NEXAB12CD",
    "activated_at": "2026-09-02T10:00:00Z",
    "activation_product_name": "NEXVIA 55-inch Ultra HD 4K Smart LED TV",
    "wallet": {
      "available_points": 25000.0,
      "pending_points": 5000.0,
      "redeemed_points": 0.0,
      "total_earned": 30000.0,
      "required_activation_stake": 10000.0,
      "free_spendable_points": 15000.0
    },
    "stake_retention_policy": {
      "required_stake_points": 10000.0,
      "rule": "The initial 20% activation credit must be maintained in your wallet. If your balance dips into this 20% deposit, your Self-Dealer status will be cancelled and require purchasing an eligible product to reactivate."
    },
    "rule_version": "Variant A (10% → 12% → 15% → 18% → 20% → Reset)"
  }
}
```

---

### 9.2 Redeem Points (With 20% Stake Retention Rule)
* **Method**: `POST`
* **URL**: `/api/customer/self-dealer/redeem` (or `/api/v1/self-dealer/redeem`)
* **Auth**: Required (`customer.auth`)

> **Critical Rule**: If the redeemed points cause the wallet balance to drop below `required_activation_stake`, Self-Dealer privileges are **instantly cancelled**. To reactivate, the customer must buy another eligible product.

#### Request Body
```json
{
  "booking_id": 88,
  "points": 5000
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Successfully redeemed 5000 points toward your booking.",
  "data": {
    "points_redeemed": 5000.0,
    "remaining_balance_due": 35000.0,
    "booking_payment_status": "partially_paid",
    "remaining_wallet_points": 20000.0,
    "self_dealer_cancelled": false,
    "self_dealer_status": "active",
    "is_self_dealer": true,
    "cancellation_notice": null
  }
}
```

---

### 9.3 Category-Wise Progression & Independent Category %
* **Method**: `GET`
* **URL**: `/api/customer/self-dealer/categories` (or `/api/v1/self-dealer/categories`)
* **Auth**: Required (`customer.auth`)

> **Independent Category Rates**:
> * Each category tracks stages **independently** (a customer can be Stage 3 in Solar, Stage 1 in EV, Stage 4 in Electronics).
> * The Admin can set an **independent percentage for each category** directly in the Admin Panel (`Admin → Categories`).
> * Example: High-ticket items like **Solar Systems** can be set to **5%**, **Electric Vehicles** to **8%**, and **Appliances** to **10%–20%**.
> * When a category-specific rate is set, any referral for that category automatically applies that customized percentage!

---

### 9.4 Apply Referral Code at Checkout
* **Method**: `POST`
* **URL**: `/api/customer/referral/apply` (or `/api/v1/referral/apply`)
* **Auth**: Required (`customer.auth`)

#### Request Body
```json
{
  "referral_code": "NEXAB12CD"
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Referral code applied successfully.",
  "data": {
    "referrer_name": "Rahul Sharma",
    "referral_code": "NEXAB12CD"
  }
}
```

---

## 10. Order Delivery Tracking

### 10.1 Multi-Item Order Checkout
* **Method**: `POST`
* **URL**: `/api/customer/orders/checkout`
* **Auth**: Required (`customer.auth`)

#### Request Body
```json
{
  "items": [
    { "product_id": 1, "quantity": 1 }
  ],
  "shipping_address": "Flat 402, Sunshine Heights, Mumbai",
  "city": "Mumbai",
  "state": "Maharashtra",
  "pincode": "400001",
  "payment_type": "down_payment",
  "use_product_credit": false
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Order placed successfully!",
  "order": {
    "order_number": "NEX-ORD-20260916-8812",
    "tracking_number": "TRK-98712345",
    "total_amount": 50000.0,
    "booking_amount": 10000.0,
    "balance_amount": 40000.0,
    "delivery_stage": "order_confirmed"
  }
}
```

---

### 10.2 Track Delivery
* **Method**: `GET`
* **URL**: `/api/deliveries/{trackingNumber}`
* **Auth**: Public

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "tracking_number": "TRK-98712345",
    "current_stage": "dispatched",
    "milestones": [
      { "stage": "order_confirmed", "completed": true, "timestamp": "2026-09-16 10:00:00" },
      { "stage": "dispatched", "completed": true, "timestamp": "2026-09-16 14:00:00" },
      { "stage": "in_transit", "completed": false },
      { "stage": "out_for_delivery", "completed": false },
      { "stage": "delivered", "completed": false }
    ]
  }
}
```

---

## 11. Warranties, Service Tickets & Installations

### 11.1 List Customer Warranties
* **Method**: `GET`
* **URL**: `/api/customer/warranties`
* **Auth**: Required (`customer.auth`)

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": [
    {
      "warranty_code": "WAR-TV-2026-9912",
      "product_name": "NEXVIA 55-inch Ultra HD 4K Smart LED TV",
      "coverage_months": 24,
      "valid_until": "2028-09-16",
      "status": "active"
    }
  ]
}
```

---

### 11.2 Create Service / Repair Ticket
* **Method**: `POST`
* **URL**: `/api/customer/service-tickets`
* **Auth**: Required (`customer.auth`)

#### Request Body
```json
{
  "warranty_id": 1,
  "issue_type": "Display issue",
  "description": "Screen flickers occasionally while streaming 4K content.",
  "preferred_date": "2026-09-20"
}
```

---

### 11.3 Schedule Free Installation
* **Method**: `POST`
* **URL**: `/api/customer/installations/schedule`
* **Auth**: Required (`customer.auth`)

#### Request Body
```json
{
  "booking_id": 88,
  "preferred_date": "2026-09-18",
  "preferred_time_slot": "10:00 AM - 01:00 PM",
  "notes": "Wall mount bracket already available."
}
```


---

## 12. CMS Dynamic Pages, Policies & Points

All pages configured and managed via the Admin Panel (Privacy Policy, Terms and Conditions, Refund Policy, About Us, etc.) can be retrieved dynamically via public REST endpoints. Each page response includes both the full formatted `content` (HTML/body text) and a structured `points` array (key highlights, terms, and policy bullet points with `title` and `description`) specifically designed for clean mobile application and web frontend consumption.

### 12.1 Fetch Privacy Policy
* **Method**: `GET` or `POST`
* **URL**: `/api/privacy-policy` (or `/api/customer/privacy-policy`)
* **Auth**: Public

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Privacy policy retrieved successfully.",
  "data": {
    "id": 1,
    "title": "Privacy Policy",
    "slug": "privacy-policy",
    "excerpt": "Learn how NEXVIA collects, protects, uses, and shares your personal information across our platform and services.",
    "content": "<h2>Privacy Policy for NEXVIA</h2><p>At NEXVIA, accessible from our official website and mobile applications, the privacy of our visitors and customers is of extreme importance...</p>",
    "points": [
      {
        "title": "Information We Collect",
        "description": "We collect personal information that you provide when registering an account, booking products, managing referral wallets, or contacting customer support (e.g. name, phone number, email address, shipping address, identity verification documents, and payment details)."
      },
      {
        "title": "How We Use Your Information",
        "description": "We utilize collected data to operate, maintain, and provide the features of NEXVIA, fulfill product bookings, process 20% advance payments and final settlements, administer referral commissions, schedule doorstep deliveries, and provide customer support."
      },
      {
        "title": "Data Protection & Security",
        "description": "We deploy industry-standard 256-bit SSL encryption, tokenized authentication, and secure access protocols to safeguard personal information from unauthorized access, alteration, disclosure, or destruction."
      },
      {
        "title": "Cookies and Tracking Technologies",
        "description": "NEXVIA uses cookies and session tokens to record visitor preferences, track active sessions, optimize website/app performance, and provide tailored product recommendations."
      },
      {
        "title": "Third-Party Service Providers",
        "description": "We do not sell your personal data. We only share necessary data with trusted third parties strictly to facilitate transactions, such as payment gateways, SMS/OTP notification providers, and verified logistics partners."
      },
      {
        "title": "User Rights & Data Control",
        "description": "You have the right to inspect, update, or request the deletion of your personal account data at any time through your account dashboard or by contacting our grievance officer."
      }
    ],
    "meta_title": "Privacy Policy - NEXVIA",
    "meta_description": "Read NEXVIA's privacy policy to understand how we protect and manage your personal data and privacy.",
    "meta_keywords": "privacy policy, nexvia privacy, data security, user rights",
    "sort_order": 1,
    "updated_at": "2026-09-17T07:33:30+00:00",
    "created_at": "2026-09-17T07:33:30+00:00"
  }
}
```

---

### 12.2 List All Active CMS Pages
* **Method**: `GET` or `POST`
* **URL**: `/api/pages` (or `/api/customer/pages`)
* **Auth**: Public

#### Response (`200 OK`)
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
      "excerpt": "Learn how NEXVIA collects, protects, uses, and shares your personal information across our platform and services.",
      "points_count": 6,
      "points": [ ... ],
      "sort_order": 1,
      "updated_at": "2026-09-17T07:33:30+00:00"
    },
    {
      "id": 2,
      "title": "Terms and Conditions",
      "slug": "terms-and-conditions",
      "excerpt": "Terms, rules, and guidelines governing user accounts, product bookings, referral credits, and platform services at NEXVIA.",
      "points_count": 5,
      "points": [ ... ],
      "sort_order": 2,
      "updated_at": "2026-09-17T07:33:30+00:00"
    }
  ]
}
```

---

### 12.3 Fetch Specific Page by Slug or ID
* **Method**: `GET` or `POST`
* **URL**: `/api/pages/{slugOrId}` (or `/api/customer/pages/{slugOrId}`)
* **Auth**: Public
* **URL Parameters**:
  * `slugOrId`: Page slug (e.g. `terms-and-conditions`, `refund-policy`, `about-us`, `contact-us`, `privacy-policy`) or numeric Database ID.

#### Example Request:
`GET /api/pages/terms-and-conditions`

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Terms and Conditions retrieved successfully.",
  "data": {
    "id": 2,
    "title": "Terms and Conditions",
    "slug": "terms-and-conditions",
    "excerpt": "Terms, rules, and guidelines governing user accounts, product bookings, referral credits, and platform services at NEXVIA.",
    "content": "<h2>Terms and Conditions of Use</h2><p>Welcome to NEXVIA. These terms and conditions outline the rules and regulations...</p>",
    "points": [
      {
        "title": "Account Registration & Eligibility",
        "description": "Users must provide accurate, verified phone numbers and identity credentials. You are responsible for safeguarding your login credentials and one-time passwords (OTPs)."
      },
      {
        "title": "20% Booking Engine & 60-Day Settlement",
        "description": "Customers can secure select catalog products by paying a 20% upfront booking deposit. The remaining 80% balance must be settled within the designated 60-day settlement window before product dispatch and doorstep delivery."
      },
      {
        "title": "Self Dealer Referral Program",
        "description": "Self-dealers earn referral rewards and tier milestones based on verified purchases. Any fraudulent self-referrals, fake accounts, or system abuse will result in commission forfeiture and account suspension."
      },
      {
        "title": "Doorstep Delivery & Installation",
        "description": "Upon complete payment verification, deliveries and scheduled technician installations are coordinated through certified logistics and service technicians."
      },
      {
        "title": "Intellectual Property",
        "description": "All trademarks, logos, system software, product designs, and content displayed on NEXVIA are the intellectual property of NEXVIA and protected under applicable laws."
      }
    ],
    "meta_title": "Terms & Conditions - NEXVIA",
    "meta_description": "Review the official terms and conditions for using NEXVIA and booking products.",
    "meta_keywords": "terms and conditions, booking rules, user agreement, nexvia terms",
    "sort_order": 2,
    "updated_at": "2026-09-17T07:33:30+00:00",
    "created_at": "2026-09-17T07:33:30+00:00"
  }
}
```

---

### 12.4 Direct Convenience Policy Shortcuts
* **Auth**: Public

| Endpoint | Method | Description |
| :--- | :--- | :--- |
| `/api/privacy-policy` | `GET`, `POST` | Fetches active Privacy Policy document & structured points |
| `/api/terms-and-conditions` | `GET`, `POST` | Fetches active Terms and Conditions document & structured points |
| `/api/terms-conditions` | `GET`, `POST` | Alias for Terms and Conditions |
| `/api/refund-policy` | `GET`, `POST` | Fetches active Refund & Cancellation Policy & structured points |
| `/api/about-us` | `GET`, `POST` | Fetches About Us company overview & points |
| `/api/contact-us` | `GET`, `POST` | Fetches Contact, Helpline & Grievance support details & points |

*(All above shortcuts are also accessible under `/api/customer/*`)*

---

## 13. Authorised Delivery & Service Partner (DSP) Ecosystem

Authorised Delivery & Service Partners (DSP) are NEXVIA's local territory hubs responsible for doorstep order deliveries, product unboxing, and certified technician installation. DSPs earn a fixed 5.0% commission on every order delivered and installed in their assigned pincodes.

### 13.1 Apply as Delivery & Service Partner (DSP)
* **Method**: `POST`
* **URL**: `/api/dsp/apply`
* **Auth**: Public

#### Request Body (Multipart / JSON)
```json
{
  "applicant_name": "Suresh Patil",
  "business_name": "Patil Logistics & Electric Mobility Hub",
  "mobile": "9876501234",
  "whatsapp": "9876501234",
  "email": "patil.dsp@example.com",
  "address": "Shop 12, Market Yard, Pune",
  "district": "Pune",
  "state": "Maharashtra",
  "pincode": "411001",
  "preferred_territory_area": "Pune Central & PCMC",
  "serviced_pincodes": "411001, 411002, 411018, 411033",
  "delivery_team_size": 4,
  "service_technicians_count": 2,
  "experience_years": 5,
  "notes": "Experienced in EV two-wheeler service and domestic appliance delivery."
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "DSP Partner application submitted successfully.",
  "data": {
    "application_number": "DSP-202609-8812",
    "status": "pending",
    "track_url": "/api/dsp/track/DSP-202609-8812"
  }
}
```

---

### 13.2 Track DSP Application Status
* **Method**: `GET`
* **URL**: `/api/dsp/track/{applicationNumber}`
* **Auth**: Public

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "application_number": "DSP-202609-8812",
    "business_name": "Patil Logistics & Electric Mobility Hub",
    "applicant_name": "Suresh Patil",
    "mobile": "9876501234",
    "status": "approved",
    "district": "Pune",
    "state": "Maharashtra",
    "serviced_pincodes": ["411001", "411002", "411018", "411033"],
    "created_at": "2026-09-18T10:00:00Z"
  }
}
```

---

### 13.3 DSP Partner Login
* **Method**: `POST`
* **URL**: `/api/dsp/login`
* **Auth**: Public

#### Request Body
```json
{
  "mobile": "9876501234",
  "password": "dsp@password123"
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "DSP Partner authenticated successfully.",
  "token": "d8f7e6c5b4a3...",
  "dsp": {
    "id": 12,
    "application_number": "DSP-202609-8812",
    "business_name": "Patil Logistics & Electric Mobility Hub",
    "applicant_name": "Suresh Patil",
    "mobile": "9876501234",
    "email": "patil.dsp@example.com",
    "territory_area": "Pune Central & PCMC",
    "serviced_pincodes": ["411001", "411002", "411018", "411033"],
    "status": "approved",
    "wallet_balance": 18500.00,
    "total_earned": 32000.00,
    "total_redeemed": 13500.00,
    "commission_rate": "5.0%"
  }
}
```

---

### 13.4 Load Nearby DSP Delivery Partners (By Pincode or User Address)
* **Method**: `GET` or `POST`
* **URL**: `/api/dsp/nearby`
* **Aliases**: `/api/customer/dsp/nearby`, `/api/dsp/available-by-pincode`
* **Auth**: Public or Customer (`customer.auth` optional)

> **Intelligent Multi-Source Detection**:
> 1. **Explicit Pincode**: Pass `pincode=411001` as query or body parameter.
> 2. **Address Text Parsing**: Pass `address="Flat 402, Sunshine Heights, MG Road, Pune, Maharashtra 411001"`. The API automatically extracts the 6-digit Indian PIN code using regex pattern matching.
> 3. **Saved Customer Profile / Address**: If authenticated and neither `pincode` nor `address` is passed, the API automatically retrieves the customer's default saved delivery address from their profile.
> 4. **Proximity-Ranked Results**:
>    - `exact_pincode` (`"Direct Area Partner"` — Directly services the user's PIN code)
>    - `district` (`"District Service Partner"` — Operating within the user's district)
>    - `state` (`"State Regional Partner"` — In the user's state)
>    - `authorized_hub` (`"Authorised Regional Hub"`)

#### Request Examples:
- `GET /api/dsp/nearby?pincode=411001`
- `POST /api/dsp/nearby` with `{"address": "Flat 402, Sunshine Heights, Pune 411001"}`
- `GET /api/customer/dsp/nearby` (automatically uses customer's default address)

#### Response (`200 OK`)
```json
{
  "status": true,
  "search_criteria": {
    "pincode": "411001",
    "district": "Pune",
    "state": "Maharashtra",
    "address_queried": "Flat 402, Sunshine Heights, Pune 411001",
    "detection_source": "extracted_from_address"
  },
  "count": 2,
  "has_direct_pincode_partner": true,
  "recommended_dsp": {
    "id": 12,
    "business_name": "Patil Logistics & Electric Mobility Hub",
    "applicant_name": "Suresh Patil",
    "mobile": "9876501234",
    "email": "patil.dsp@example.com",
    "territory_area": "Pune Central & PCMC",
    "district": "Pune",
    "state": "Maharashtra",
    "premises_address": "Shop 12, Market Yard, Pune",
    "premises_pincode": "411001",
    "serviced_pincodes": ["411001", "411002", "411018", "411033"],
    "match_type": "exact_pincode",
    "match_label": "Direct Area Partner",
    "is_direct_match": true,
    "is_recommended": true,
    "technicians_count": 2,
    "vehicles_count": 4,
    "commission_rate": "5.0%"
  },
  "nearby_dsps": [
    {
      "id": 12,
      "business_name": "Patil Logistics & Electric Mobility Hub",
      "applicant_name": "Suresh Patil",
      "mobile": "9876501234",
      "email": "patil.dsp@example.com",
      "territory_area": "Pune Central & PCMC",
      "district": "Pune",
      "state": "Maharashtra",
      "premises_address": "Shop 12, Market Yard, Pune",
      "premises_pincode": "411001",
      "serviced_pincodes": ["411001", "411002", "411018", "411033"],
      "match_type": "exact_pincode",
      "match_label": "Direct Area Partner",
      "is_direct_match": true,
      "is_recommended": true,
      "technicians_count": 2,
      "vehicles_count": 4,
      "commission_rate": "5.0%"
    },
    {
      "id": 18,
      "business_name": "Western Maharashtra Service Hub",
      "applicant_name": "Vikas Shinde",
      "mobile": "9822000000",
      "email": "shinde@example.com",
      "territory_area": "Pune District",
      "district": "Pune",
      "state": "Maharashtra",
      "premises_address": "Plot 55, MIDC Bhosari, Pune",
      "premises_pincode": "411026",
      "serviced_pincodes": ["411026", "411039"],
      "match_type": "district",
      "match_label": "District Service Partner",
      "is_direct_match": false,
      "is_recommended": false,
      "technicians_count": 5,
      "vehicles_count": 6,
      "commission_rate": "5.0%"
    }
  ]
}
```

---

### 13.5 DSP Partner Dashboard
* **Method**: `GET` or `POST`
* **URL**: `/api/dsp/dashboard`
* **Auth**: Required (`dsp.auth` — `Bearer <api_token>` or `token` or `dsp_id`)

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "dsp": {
      "id": 12,
      "business_name": "Patil Logistics & Electric Mobility Hub",
      "status": "approved"
    },
    "metrics": {
      "assigned_deliveries": 8,
      "in_transit": 2,
      "completed_deliveries": 15,
      "wallet_balance": 18500.00,
      "total_earned": 32000.00
    },
    "recent_deliveries": [
      {
        "id": 105,
        "tracking_number": "NEX-DEL-2026-9901",
        "booking_number": "BK-20260916-7788",
        "product_name": "NEXVIA 55-inch Ultra HD 4K Smart LED TV",
        "customer_name": "Rahul Sharma",
        "status": "in_transit",
        "commission_amount": 2500.00
      }
    ]
  }
}
```

---

### 13.6 DSP Profile (View & Update)
* **View Profile**: `GET /api/dsp/profile`
* **Update Profile**: `POST /api/dsp/profile`
* **Auth**: Required (`dsp.auth`)

#### Request Body (Update)
```json
{
  "business_name": "Patil Express Logistics",
  "whatsapp": "9876501234",
  "delivery_team_size": 6,
  "service_technicians_count": 3
}
```

---

### 13.7 DSP Assigned Deliveries List
* **Method**: `GET` or `POST`
* **URL**: `/api/dsp/deliveries`
* **Auth**: Required (`dsp.auth`)
* **Query Parameters**:
  * `status`: `all`, `assigned`, `in_transit`, `delivered`, `cancelled`
  * `per_page`: `20`
  * `page`: `1`

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": [
    {
      "id": 105,
      "tracking_number": "NEX-DEL-2026-9901",
      "booking_number": "BK-20260916-7788",
      "customer_name": "Rahul Sharma",
      "customer_phone": "9876543210",
      "shipping_address": "Flat 402, Sunshine Heights, Mumbai",
      "pincode": "411001",
      "product_name": "NEXVIA 55-inch Ultra HD 4K Smart LED TV",
      "quantity": 1,
      "status": "in_transit",
      "order_amount": 50000.00,
      "dsp_commission": 2500.00,
      "assigned_at": "2026-09-17T09:00:00Z"
    }
  ]
}
```

---

### 13.8 DSP Delivery Detail
* **Method**: `GET` or `POST`
* **URL**: `/api/dsp/deliveries/{id}`
* **Auth**: Required (`dsp.auth`)

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "id": 105,
    "tracking_number": "NEX-DEL-2026-9901",
    "booking_number": "BK-20260916-7788",
    "customer": {
      "name": "Rahul Sharma",
      "phone": "9876543210",
      "shipping_address": "Flat 402, Sunshine Heights, Mumbai",
      "city": "Pune",
      "state": "Maharashtra",
      "pincode": "411001"
    },
    "product": {
      "id": 1,
      "title": "NEXVIA 55-inch Ultra HD 4K Smart LED TV",
      "model_code": "NEX-TV55-4K",
      "color": "Midnight Black"
    },
    "delivery_status": "in_transit",
    "delivery_boy_name": "Karan Singh",
    "delivery_boy_phone": "9876509999",
    "estimated_delivery_date": "2026-09-22",
    "dsp_commission_amount": 2500.00,
    "dsp_commission_paid": false
  }
}
```

---

### 13.9 Update Delivery & Installation Status
* **Method**: `POST`
* **URL**: `/api/dsp/deliveries/{id}/status`
* **Auth**: Required (`dsp.auth`)

> **Automatic Earnings Credit**: When the status is set to `delivered` or `installed`, the 5.0% DSP partner commission is automatically credited to the DSP's wallet ledger!

#### Request Body
```json
{
  "status": "delivered",
  "delivery_boy_name": "Karan Singh",
  "delivery_boy_phone": "9876509999",
  "delivery_notes": "Delivered in original box and unboxed in customer presence. Verified OTP.",
  "otp": "452189"
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Delivery status updated to delivered. DSP commission of ₹2,500.00 credited to wallet.",
  "data": {
    "delivery_id": 105,
    "status": "delivered",
    "delivered_at": "2026-09-21T11:20:00Z",
    "commission_credited": 2500.00,
    "new_dsp_wallet_balance": 21000.00
  }
}
```

---

### 13.10 DSP Earnings & Wallet Ledger
* **Method**: `GET` or `POST`
* **URL**: `/api/dsp/wallet`
* **Auth**: Required (`dsp.auth`)

#### Response (`200 OK`)
```json
{
  "status": true,
  "wallet": {
    "balance": 21000.00,
    "total_earned": 34500.00,
    "total_redeemed": 13500.00
  },
  "transactions": [
    {
      "id": 501,
      "type": "credit",
      "amount": 2500.00,
      "description": "5.0% Delivery & Service Commission on #BK-20260916-7788",
      "reference_id": 105,
      "balance_after": 21000.00,
      "created_at": "2026-09-21T11:20:00Z"
    }
  ]
}
```

---

### 13.11 Request DSP Payout / Withdrawal
* **Method**: `POST`
* **URL**: `/api/dsp/wallet/redeem`
* **Auth**: Required (`dsp.auth`)

#### Request Body
```json
{
  "amount": 10000.00,
  "payment_mode": "bank_transfer",
  "bank_account_number": "123456789012",
  "bank_ifsc": "HDFC0001234",
  "bank_beneficiary_name": "Patil Logistics",
  "upi_id": null
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Payout request for ₹10,000.00 submitted successfully.",
  "data": {
    "request_id": 44,
    "amount": 10000.00,
    "status": "pending",
    "remaining_wallet_balance": 11000.00
  }
}
```

---

### 13.12 List DSP Payout Requests
* **Method**: `GET` or `POST`
* **URL**: `/api/dsp/wallet/payout-requests`
* **Auth**: Required (`dsp.auth`)

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": [
    {
      "id": 44,
      "amount": 10000.00,
      "status": "pending",
      "payment_mode": "bank_transfer",
      "created_at": "2026-09-21T11:25:00Z"
    }
  ]
}
```

---

### 13.13 List Assigned Service & Problem Requests
* **Method**: `GET` or `POST`
* **URL**: `/api/dsp/service-requests`
* **Auth**: Required (`dsp.auth` — `Bearer <dsp_api_token>` or `token`)
* **Query Parameters**:
  * `attended`: `all`, `yes` (attended by partner/tech), `no` (pending partner attention)
  * `status`: `all`, `open`, `attended`, `in_progress`, `resolved`, `cancelled`
  * `search`: Search by ticket number, customer name, mobile, address, or PIN
  * `per_page`: Number of records per page (default: `20`)
  * `page`: Page number

#### Response (`200 OK`)
```json
{
  "status": true,
  "success": true,
  "stats": {
    "total_assigned": 5,
    "attended_count": 3,
    "pending_attention": 2,
    "resolved_count": 1
  },
  "data": [
    {
      "id": 15,
      "ticket_number": "TKT-2026-70747",
      "customer_name": "Rohan Sharma",
      "customer_phone": "9822113355",
      "address": "Plot 42, Anand Nagar, Gangapur Road",
      "pincode": "422001",
      "city": "Nashik",
      "state": "Maharashtra",
      "subject": "Vehicle display not turning on",
      "service_type": "breakdown",
      "priority": "high",
      "status": "open",
      "attendance": {
        "is_attended": false,
        "attended_at": null,
        "attended_by_name": null,
        "attended_by_phone": null
      },
      "dsp_notes": null,
      "created_at": "2026-09-26T05:20:00+00:00"
    }
  ],
  "pagination": {
    "total": 5,
    "per_page": 20,
    "current_page": 1,
    "last_page": 1
  }
}
```

---

### 13.14 View Service Request Details
* **Method**: `GET` or `POST`
* **URL**: `/api/dsp/service-requests/{id}`
* **Auth**: Required (`dsp.auth`)

#### Response (`200 OK`)
```json
{
  "status": true,
  "success": true,
  "data": {
    "id": 15,
    "ticket_number": "TKT-2026-70747",
    "customer": {
      "name": "Rohan Sharma",
      "phone": "9822113355",
      "address": "Plot 42, Anand Nagar, Gangapur Road",
      "pincode": "422001",
      "city": "Nashik",
      "state": "Maharashtra",
      "maps_url": "https://maps.google.com/?q=Plot+42%2C+Anand+Nagar%2C+Gangapur+Road%2C+Nashik+-+422001"
    },
    "issue": {
      "subject": "Vehicle display not turning on",
      "service_type": "breakdown",
      "priority": "high",
      "details": "Turned key on this morning, display is completely blank. Checked ignition switch.",
      "attachments": [
        "http://127.0.0.1:8000/uploads/service_attachments/photo_1727328000_abc123.jpg"
      ]
    },
    "booking": {
      "id": 42,
      "booking_number": "BK-20260920-1122",
      "product_name": "NEXVIA Storm E-Scooter (Midnight Black)"
    },
    "status": "open",
    "attendance": {
      "is_attended": false,
      "attended_at": null,
      "attended_by_name": null,
      "attended_by_phone": null
    },
    "dsp_notes": null,
    "resolution": {
      "resolved_at": null,
      "resolution_notes": null,
      "resolution_proof_url": null
    },
    "created_at": "2026-09-26T05:20:00+00:00"
  }
}
```

---

### 13.15 Update Attendance, Technician & Status
* **Method**: `POST`
* **URL**: `/api/dsp/service-requests/{id}/status`
* **Auth**: Required (`dsp.auth`)

> **Attendance & Resolution Tracking**:
> When a DSP or technician visits or contacts the customer, send `is_attended: 1` or change status to `attended` / `in_progress`.
> When closing the ticket, set `status: "resolved"` and optionally upload `resolution_proof` (image or signed service job card).

#### Request Body (Multipart / JSON)
```json
{
  "is_attended": 1,
  "status": "in_progress",
  "attended_by_name": "Vijay Patil (Senior Tech)",
  "attended_by_phone": "9823000000",
  "dsp_notes": "Technician arrived on site. Identified minor wiring harness disconnect.",
  "resolution_notes": "Wiring harness reconnected and secured with heat shrink tubing.",
  "resolution_proof": "(binary image file, optional)"
}
```

#### Fields Description:
* `is_attended` *(boolean, optional)*: `1` or `true` marks ticket attended by DSP partner.
* `status` *(string, optional)*: `open`, `attended`, `in_progress`, `resolved`, `cancelled`.
* `attended_by_name` *(string, optional)*: Name of attending technician/service engineer.
* `attended_by_phone` *(string, optional)*: Contact mobile of attending technician.
* `dsp_notes` *(string, optional)*: Territory progress notes or diagnosis observations.
* `resolution_notes` *(string, optional)*: Description of service fix or part replacement when closing ticket.
* `resolution_proof` *(file, optional)*: Photo of completed work or signed customer job sheet.

#### Response (`200 OK`)
```json
{
  "status": true,
  "success": true,
  "message": "Service ticket #TKT-2026-70747 updated successfully.",
  "data": {
    "id": 15,
    "ticket_number": "TKT-2026-70747",
    "status": "in_progress",
    "attendance": {
      "is_attended": true,
      "attended_at": "2026-09-26T05:23:30+00:00",
      "attended_by_name": "Vijay Patil (Senior Tech)",
      "attended_by_phone": "9823000000"
    },
    "dsp_notes": "Technician arrived on site. Identified minor wiring harness disconnect.",
    "resolved_at": null,
    "resolution_notes": null,
    "resolution_proof": null
  }
}
```

---

### 13.16 DSP Logout
* **Method**: `POST`
* **URL**: `/api/dsp/logout`
* **Auth**: Required (`dsp.auth`)

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "DSP Partner logged out successfully."
}
```

---

## 14. DLS Agro & Farm Equipment APIs

Dedicated endpoints for accessing DLS Agro & Farm Equipment catalog (e.g., Solar Water Pumps, Heavy Duty Power Tillers, Cultivators, High-Precision Agri Drones).

> **Interchangeable URL Prefixes**: Both `/api/dls-agro/*` and `/api/dls-farm/*` prefixes are supported interchangeably.  
> **Global Type Filtering**: The standard catalog APIs `/api/products` and `/api/categories` also support `?type=dls_farm_equipment` (or `?type=agro`) to filter specifically for DLS Agro items.

---

### 14.1 List DLS Agro Categories
* **Method**: `GET` or `POST`
* **URL**: `/api/dls-agro/categories` (or `/api/dls-farm/categories`)
* **Auth**: Public

#### Query / Request Body Parameters
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `search` | String | No | Search keyword matching category name, slug, or referral code. |

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "DLS Agro categories retrieved successfully.",
  "count": 3,
  "data": [
    {
      "id": 10,
      "name": "Solar Water Pumps",
      "slug": "solar-water-pumps",
      "type": "dls_farm_equipment",
      "referral_category_code": "SOLPUMP",
      "referral_eligible": true,
      "commission_percentage": 10.00,
      "description": "High efficiency AC/DC submersible solar agricultural pumps with MPPT controller",
      "image": "http://127.0.0.1:8000/storage/categories/solar-pump.jpg",
      "image_url": "http://127.0.0.1:8000/storage/categories/solar-pump.jpg",
      "sort_order": 1,
      "products_count": 5
    },
    {
      "id": 11,
      "name": "Power Tillers & Cultivators",
      "slug": "power-tillers-cultivators",
      "type": "dls_farm_equipment",
      "referral_category_code": "TILLER",
      "referral_eligible": true,
      "commission_percentage": 12.00,
      "description": "Heavy duty diesel and electric power tillers for dryland and wetland farming",
      "image": "http://127.0.0.1:8000/storage/categories/tiller.jpg",
      "image_url": "http://127.0.0.1:8000/storage/categories/tiller.jpg",
      "sort_order": 2,
      "products_count": 4
    },
    {
      "id": 12,
      "name": "Sprayers & Agricultural Drones",
      "slug": "sprayers-agri-drones",
      "type": "dls_farm_equipment",
      "referral_category_code": "DRONE",
      "referral_eligible": true,
      "commission_percentage": 15.00,
      "description": "Battery-operated high precision crop sprayers and automated agricultural payload drones",
      "image": "http://127.0.0.1:8000/storage/categories/drone.jpg",
      "image_url": "http://127.0.0.1:8000/storage/categories/drone.jpg",
      "sort_order": 3,
      "products_count": 3
    }
  ]
}
```

---

### 14.2 List DLS Agro Products
* **Method**: `GET` or `POST`
* **URL**: `/api/dls-agro/products` (or `/api/dls-farm/products`)
* **Auth**: Public

#### Query / Request Body Parameters
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `category_id` / `category` | Integer / String | No | Filter by category ID or category slug (e.g. `solar-water-pumps`). |
| `search` | String | No | Search keyword in product name, SKU, model code, or offer text. |
| `sortBy` | String | No | Sort option: `price_low`, `price_high`, `featured`, `trending`, or default (latest). |
| `page` | Integer | No | Page number (default: `1`). |
| `limit` / `per_page` | Integer | No | Items per page (default: `10`, max: `100`). |

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "DLS Agro products retrieved successfully.",
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 2,
    "has_more": false
  },
  "data": [
    {
      "id": 20,
      "name": "DLS 5HP Solar Submersible Water Pump",
      "slug": "dls-5hp-solar-submersible-pump",
      "model_code": "DLS-SOL-5000",
      "sku": "SKU-FARM-PUMP-01",
      "product_type": "dls_farm_equipment",
      "category": {
        "id": 10,
        "name": "Solar Water Pumps",
        "slug": "solar-water-pumps",
        "type": "dls_farm_equipment",
        "referral_category_code": "SOLPUMP"
      },
      "mrp": 85000.00,
      "booking_percentage": 20.00,
      "booking_amount": 17000.00,
      "balance_amount": 68000.00,
      "stock": 12,
      "main_image": "http://127.0.0.1:8000/storage/products/solar-pump.jpg",
      "imageUrl": "http://127.0.0.1:8000/storage/products/solar-pump.jpg",
      "images": ["http://127.0.0.1:8000/storage/products/solar-pump.jpg"],
      "gallery": ["http://127.0.0.1:8000/storage/products/solar-pump.jpg"],
      "video_url": "https://www.youtube.com/watch?v=example",
      "offer_text": "Govt Subsidy Assistance Available",
      "warranty_info": "5 Years Manufacturer Warranty on Pump & Controller",
      "installation_info": "Free On-site Farm Installation & Borehole Testing",
      "is_featured": true,
      "status": "active",
      "eligible_referral_value": 85000.00,
      "referral_eligible": true,
      "self_dealer_eligible": true,
      "created_at": "2026-09-22T08:00:00Z"
    }
  ]
}
```

---

### 14.3 Featured DLS Agro Products
* **Method**: `GET` or `POST`
* **URL**: `/api/dls-agro/products/featured` (or `/api/dls-farm/products/featured`)
* **Auth**: Public

#### Query Parameters
| Field | Type | Required | Description |
| :--- | :--- | :--- | :--- |
| `limit` | Integer | No | Maximum number of products to return (default: `10`, max: `50`). |

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Featured DLS Agro products retrieved successfully.",
  "count": 2,
  "data": [
    {
      "id": 20,
      "name": "DLS 5HP Solar Submersible Water Pump",
      "slug": "dls-5hp-solar-submersible-pump",
      "model_code": "DLS-SOL-5000",
      "sku": "SKU-FARM-PUMP-01",
      "product_type": "dls_farm_equipment",
      "mrp": 85000.00,
      "booking_amount": 17000.00,
      "balance_amount": 68000.00,
      "is_featured": true
    }
  ]
}
```

---

### 14.4 DLS Agro Product Details
* **Method**: `GET` or `POST`
* **URL**: `/api/dls-agro/products/{idOrSlug}` (or `/api/dls-farm/products/{idOrSlug}`)
* **Auth**: Public

#### Path Parameter
| Parameter | Type | Description |
| :--- | :--- | :--- |
| `idOrSlug` | Integer / String | Product numeric ID (e.g. `20`) or URL slug (e.g. `dls-5hp-solar-submersible-pump`). |

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "DLS Agro product details retrieved successfully.",
  "data": {
    "id": 20,
    "name": "DLS 5HP Solar Submersible Water Pump",
    "slug": "dls-5hp-solar-submersible-pump",
    "model_code": "DLS-SOL-5000",
    "sku": "SKU-FARM-PUMP-01",
    "product_type": "dls_farm_equipment",
    "overview": "High-efficiency deep well solar submersible water pump engineered for agricultural boreholes, drip irrigation, and micro-sprinklers. Equipped with MPPT auto dry-run tracking to operate directly from solar panels without battery maintenance.",
    "description": "High-efficiency deep well solar submersible water pump engineered for agricultural boreholes, drip irrigation, and micro-sprinklers. Equipped with MPPT auto dry-run tracking to operate directly from solar panels without battery maintenance.",
    "category": {
      "id": 10,
      "name": "Solar Water Pumps",
      "slug": "solar-water-pumps",
      "type": "dls_farm_equipment",
      "referral_category_code": "SOLPUMP"
    },
    "mrp": 85000.00,
    "booking_percentage": 20.00,
    "booking_amount": 17000.00,
    "balance_amount": 68000.00,
    "balance_due_days": 60,
    "pricing": {
      "mrp": 85000.00,
      "booking_percentage": 20.00,
      "booking_amount": 17000.00,
      "balance_amount": 68000.00,
      "balance_due_days": 60,
      "currency": "INR",
      "currency_symbol": "₹",
      "pricing_summary": "Pay ₹17,000.00 (20%) now, balance ₹68,000.00 within 60 days."
    },
    "referral_eligible": true,
    "self_dealer_eligible": true,
    "eligible_referral_value": 85000.00,
    "stock": 12,
    "in_stock": true,
    "stock_status": "In Stock",
    "main_image": "http://127.0.0.1:8000/storage/products/solar-pump.jpg",
    "imageUrl": "http://127.0.0.1:8000/storage/products/solar-pump.jpg",
    "gallery": [
      "http://127.0.0.1:8000/storage/products/solar-pump-1.jpg",
      "http://127.0.0.1:8000/storage/products/solar-pump-2.jpg"
    ],
    "images": [
      "http://127.0.0.1:8000/storage/products/solar-pump-1.jpg",
      "http://127.0.0.1:8000/storage/products/solar-pump-2.jpg"
    ],
    "video_url": "https://www.youtube.com/watch?v=example",
    "offer_text": "Govt Subsidy Assistance Available",
    "features": [
      "5HP 3-Phase Submersible DC Motor",
      "MPPT Smart Solar Inverter / Controller Included",
      "Stainless Steel 304 High-Durability Impeller",
      "Auto Dry-Run & Reverse Polarity Protection"
    ],
    "key_features": [
      "5HP 3-Phase Submersible DC Motor",
      "MPPT Smart Solar Inverter / Controller Included",
      "Stainless Steel 304 High-Durability Impeller",
      "Auto Dry-Run & Reverse Polarity Protection"
    ],
    "technical_specifications": [
      { "name": "Power Rating", "value": "5 HP (3.7 kW)" },
      { "name": "Discharge Capacity", "value": "25,000 Liters/Hour" },
      { "name": "Max Head Depth", "value": "120 meters" },
      { "name": "Solar Array Required", "value": "4800W - 6000W DC" }
    ],
    "specifications": [
      { "name": "Power Rating", "value": "5 HP (3.7 kW)" },
      { "name": "Discharge Capacity", "value": "25,000 Liters/Hour" },
      { "name": "Max Head Depth", "value": "120 meters" },
      { "name": "Solar Array Required", "value": "4800W - 6000W DC" }
    ],
    "specs": {
      "Power Rating": "5 HP (3.7 kW)",
      "Discharge Capacity": "25,000 Liters/Hour",
      "Max Head Depth": "120 meters",
      "Solar Array Required": "4800W - 6000W DC"
    },
    "warranty_info": "5 Years Manufacturer Warranty on Pump & Controller",
    "installation_info": "Free On-site Farm Installation & Borehole Testing",
    "delivery_info": "Delivered by Local Authorised Delivery & Service Partner (DSP)",
    "is_featured": true,
    "status": "active",
    "created_at": "2026-09-22T08:00:00Z"
  }
}
```

---

### 14.5 Querying Standard Catalog with `?type=` Filter
You can also filter the primary `/api/products` and `/api/categories` endpoints using the `type` query parameter:

* `GET /api/products?type=dls_farm_equipment` (or `?type=agro`): Returns **only** DLS Agro / Farm Equipment products.
* `GET /api/products?type=standard` (or `?type=regular`): Returns **only** non-agro / standard electronics and appliances.
* `GET /api/categories?type=dls_farm_equipment`: Returns **only** DLS Agro categories.
* `GET /api/categories?type=standard`: Returns **only** standard electronics / appliance categories.

---

## 15. DSP Service & Problem Requests Management (Attendance, Location Routing & Resolution)

NEXVIA connects customer service and problem requests directly to regional Authorised Delivery & Service Partners (DSPs) based on customer territory and PIN code. DSPs handle doorstep deliveries, product servicing, warranty troubleshooting, and certified repair attendance.

This section covers:
- **Location-Based DSP Routing**: Customer complaints, breakdowns, or service tickets are automatically routed to the local DSP servicing that PIN code.
- **Attendance Tracking**: DSPs maintain whether a technician has contacted or physically attended the request (`is_attended: true` with timestamp, technician name & phone).
- **Resolution Proof**: DSPs upload resolution remarks and image proof (work photos, signed customer job sheet) when closing tickets.
- **Unified Visibility**: Admins, DSPs, and Customers can track live attendance and progress at every stage.

---

### 15.1 Customer Submit Service / Problem Request (With DSP Auto-Allocation)
* **Method**: `POST`
* **URL**: `/api/customer/service-requests` (or `/api/customer/service-tickets`)
* **Auth**: Required (`customer.auth` — `Bearer <api_token>` header or `token` parameter)

#### Location-Based Routing Logic:
1. The API inspects the provided `pincode`, `city`, and `state` (or retrieves them automatically from the associated `booking_id` or customer profile).
2. It queries all approved Delivery & Service Partners (DSPs) to find the best match for that PIN code or district.
3. If matched, the request is instantly allocated to that DSP with `is_attended: false` and `status: open`.
4. If no local partner directly services the PIN code, the ticket is registered for Admin dispatch and regional hub assignment.

#### Request Body (Multipart / JSON)
```json
{
  "subject": "Vehicle display not turning on",
  "service_type": "breakdown",
  "priority": "high",
  "details": "Turned key on this morning, display is completely blank. Checked ignition switch.",
  "booking_id": 42,
  "customer_name": "Rohan Sharma",
  "customer_phone": "9822113355",
  "address": "Plot 42, Anand Nagar, Gangapur Road",
  "city": "Nashik",
  "state": "Maharashtra",
  "pincode": "422001",
  "photo": "(binary image file, optional)",
  "video": "(binary video file, optional)",
  "invoice": "(binary document file, optional)"
}
```

#### Fields Description:
* `subject` *(string, required)*: Brief summary of the complaint or service request.
* `service_type` *(string, required)*: Type of service requested (`breakdown`, `problem`, `repair`, `warranty`, `maintenance`, `installation`, `replacement`, `technical_support`, `complaint`, `other`).
* `priority` *(string, optional)*: Priority level (`low`, `medium`, `high`, `urgent`). Default: `medium`.
* `details` or `description` *(string, required)*: Detailed description of the problem or breakdown.
* `pincode` *(string, optional)*: 6-digit Indian PIN code. Used for automatic DSP territory matching. If omitted, uses default address from customer profile.
* `city`, `state`, `address` *(string, optional)*: Doorstep service location.
* `booking_id` *(integer, optional)*: Associated vehicle/product booking ID.
* `photo`, `video`, `invoice` *(files, optional)*: Issue photos, videos, or invoices.

#### Response (`201 Created`)
```json
{
  "status": true,
  "success": true,
  "message": "Service request submitted successfully. Allocated to Authorised DSP 'Patil Motors & Electronic Services' for PIN 422001.",
  "data": {
    "id": 15,
    "ticket_number": "TKT-2026-70747",
    "subject": "Vehicle display not turning on",
    "service_type": "breakdown",
    "priority": "high",
    "status": "open",
    "is_attended": false,
    "location": {
      "address": "Plot 42, Anand Nagar, Gangapur Road",
      "pincode": "422001",
      "city": "Nashik",
      "state": "Maharashtra"
    },
    "allocated_dsp": {
      "id": 1,
      "business_name": "Patil Motors & Electronic Services",
      "contact_name": "Suresh Patil",
      "mobile": "9821098765",
      "district": "Nashik",
      "state": "Maharashtra"
    },
    "created_at": "2026-09-26 10:50:00"
  }
}
```

---

### 15.2 Customer List Service Requests & Attendance Status
* **Method**: `GET`
* **URL**: `/api/customer/service-requests` (or `/api/customer/service-tickets`)
* **Auth**: Required (`customer.auth`)

#### Response (`200 OK`)
```json
{
  "status": true,
  "success": true,
  "data": [
    {
      "id": 15,
      "ticket_number": "TKT-2026-70747",
      "subject": "Vehicle display not turning on",
      "service_type": "breakdown",
      "priority": "high",
      "status": "in_progress",
      "is_attended": true,
      "attended_at": "2026-09-26T05:23:30+00:00",
      "technician_name": "Vijay Patil (Senior Tech)",
      "technician_phone": "9823000000",
      "allocated_dsp": {
        "id": 1,
        "business_name": "Patil Motors & Electronic Services",
        "contact_name": "Suresh Patil",
        "mobile": "9821098765"
      },
      "resolved_at": null,
      "created_at": "2026-09-26T05:20:00+00:00"
    }
  ]
}
```

---

### 15.3 Customer View Single Service Request Detail
* **Method**: `GET`
* **URL**: `/api/customer/service-requests/{id}` (or `/api/customer/service-tickets/{id}`)
* **Auth**: Required (`customer.auth`)

#### Response (`200 OK`)
```json
{
  "status": true,
  "success": true,
  "data": {
    "id": 15,
    "ticket_number": "TKT-2026-70747",
    "subject": "Vehicle display not turning on",
    "service_type": "breakdown",
    "priority": "high",
    "status": "resolved",
    "details": "Turned key on this morning, display is completely blank.",
    "is_attended": true,
    "attended_at": "2026-09-26T05:23:30+00:00",
    "attended_by_name": "Vijay Patil (Senior Tech)",
    "attended_by_phone": "9823000000",
    "dsp_notes": "Technician arrived on site. Identified minor wiring harness disconnect.",
    "resolved_at": "2026-09-26T06:15:00+00:00",
    "resolution_notes": "Wiring harness reconnected and secured with heat shrink tubing.",
    "resolution_proof_url": "http://127.0.0.1:8000/uploads/service_attachments/res_proof_15.jpg",
    "allocated_dsp": {
      "id": 1,
      "business_name": "Patil Motors & Electronic Services",
      "contact_name": "Suresh Patil",
      "mobile": "9821098765",
      "district": "Nashik"
    }
  }
}
```

---

### 15.4 Customer Lookup Local DSP for Pincode
* **Method**: `GET`
* **URL**: `/api/customer/dsp/lookup?pincode=422001`
* **Auth**: Public or Customer (`customer.auth` optional)

#### Response (`200 OK`)
```json
{
  "status": true,
  "success": true,
  "pincode": "422001",
  "dsp_available": true,
  "dsp": {
    "id": 1,
    "business_name": "Patil Motors & Electronic Services",
    "contact_name": "Suresh Patil",
    "mobile": "9821098765",
    "district": "Nashik",
    "state": "Maharashtra",
    "serviced_pincodes": ["422001", "422002", "422003"]
  }
}
```

---

### 15.5 DSP Partner List Assigned Requests & Live Summary Stats
* **Method**: `GET` or `POST`
* **URL**: `/api/dsp/service-requests`
* **Auth**: Required (`dsp.auth` — `Bearer <dsp_api_token>` header or `token` parameter)
* **Query Parameters**:
  * `attended`: `all`, `yes` (attended by partner/tech), `no` (pending partner attention)
  * `status`: `all`, `open`, `attended`, `in_progress`, `resolved`, `cancelled`
  * `search`: Search by ticket number, customer name, mobile, address, or PIN
  * `per_page`: Number of records per page (default: `20`)
  * `page`: Page number

#### Response (`200 OK`)
```json
{
  "status": true,
  "success": true,
  "stats": {
    "total_assigned": 5,
    "attended_count": 3,
    "pending_attention": 2,
    "resolved_count": 1
  },
  "data": [
    {
      "id": 15,
      "ticket_number": "TKT-2026-70747",
      "customer_name": "Rohan Sharma",
      "customer_phone": "9822113355",
      "address": "Plot 42, Anand Nagar, Gangapur Road",
      "pincode": "422001",
      "city": "Nashik",
      "state": "Maharashtra",
      "subject": "Vehicle display not turning on",
      "service_type": "breakdown",
      "priority": "high",
      "status": "open",
      "attendance": {
        "is_attended": false,
        "attended_at": null,
        "attended_by_name": null,
        "attended_by_phone": null
      },
      "dsp_notes": null,
      "created_at": "2026-09-26T05:20:00+00:00"
    }
  ],
  "pagination": {
    "total": 5,
    "per_page": 20,
    "current_page": 1,
    "last_page": 1
  }
}
```

---

### 15.6 DSP Partner View Request Details & Customer Location
* **Method**: `GET` or `POST`
* **URL**: `/api/dsp/service-requests/{id}`
* **Auth**: Required (`dsp.auth`)

#### Response (`200 OK`)
```json
{
  "status": true,
  "success": true,
  "data": {
    "id": 15,
    "ticket_number": "TKT-2026-70747",
    "customer": {
      "name": "Rohan Sharma",
      "phone": "9822113355",
      "address": "Plot 42, Anand Nagar, Gangapur Road",
      "pincode": "422001",
      "city": "Nashik",
      "state": "Maharashtra",
      "maps_url": "https://maps.google.com/?q=Plot+42%2C+Anand+Nagar%2C+Gangapur+Road%2C+Nashik+-+422001"
    },
    "issue": {
      "subject": "Vehicle display not turning on",
      "service_type": "breakdown",
      "priority": "high",
      "details": "Turned key on this morning, display is completely blank. Checked ignition switch.",
      "attachments": [
        "http://127.0.0.1:8000/uploads/service_attachments/photo_1727328000_abc123.jpg"
      ]
    },
    "booking": {
      "id": 42,
      "booking_number": "BK-20260920-1122",
      "product_name": "NEXVIA Storm E-Scooter (Midnight Black)"
    },
    "status": "open",
    "attendance": {
      "is_attended": false,
      "attended_at": null,
      "attended_by_name": null,
      "attended_by_phone": null
    },
    "dsp_notes": null,
    "resolution": {
      "resolved_at": null,
      "resolution_notes": null,
      "resolution_proof_url": null
    },
    "created_at": "2026-09-26T05:20:00+00:00"
  }
}
```

---

### 15.7 DSP Partner Update Attendance, Technician & Status
* **Method**: `POST`
* **URL**: `/api/dsp/service-requests/{id}/status`
* **Auth**: Required (`dsp.auth`)

> **Attendance & Resolution Tracking**:
> When a DSP or technician visits or contacts the customer, send `is_attended: 1` or change status to `attended` / `in_progress`.
> When closing the ticket, set `status: "resolved"` and upload `resolution_proof` (image or signed service job card).

#### Request Body (Multipart / JSON)
```json
{
  "is_attended": 1,
  "status": "in_progress",
  "attended_by_name": "Vijay Patil (Senior Tech)",
  "attended_by_phone": "9823000000",
  "dsp_notes": "Technician arrived on site. Identified minor wiring harness disconnect.",
  "resolution_notes": "Wiring harness reconnected and secured with heat shrink tubing.",
  "resolution_proof": "(binary image file, optional)"
}
```

#### Fields Description:
* `is_attended` *(boolean, optional)*: `1` or `true` marks ticket attended by DSP partner.
* `status` *(string, optional)*: `open`, `attended`, `in_progress`, `resolved`, `cancelled`.
* `attended_by_name` *(string, optional)*: Name of attending technician/service engineer.
* `attended_by_phone` *(string, optional)*: Contact mobile of attending technician.
* `dsp_notes` *(string, optional)*: Territory progress notes or diagnosis observations.
* `resolution_notes` *(string, optional)*: Description of service fix or part replacement when closing ticket.
* `resolution_proof` *(file, optional)*: Photo of completed work or signed customer job sheet.

#### Response (`200 OK`)
```json
{
  "status": true,
  "success": true,
  "message": "Service ticket #TKT-2026-70747 updated successfully.",
  "data": {
    "id": 15,
    "ticket_number": "TKT-2026-70747",
    "status": "in_progress",
    "attendance": {
      "is_attended": true,
      "attended_at": "2026-09-26T05:23:30+00:00",
      "attended_by_name": "Vijay Patil (Senior Tech)",
      "attended_by_phone": "9823000000"
    },
    "dsp_notes": "Technician arrived on site. Identified minor wiring harness disconnect.",
    "resolved_at": null,
    "resolution_notes": null,
    "resolution_proof": null
  }
}
```

---

## Summary of Error Status Codes

| Code | Status | Meaning |
| :--- | :--- | :--- |
| `200` | **OK** | Request succeeded. |
| `401` | **Unauthenticated** | Missing or invalid API token / Customer ID. |
| `403` | **Forbidden** | Account inactive or insufficient privileges. |
| `404` | **Not Found** | Requested resource (Product, Booking, User) does not exist. |
| `422` | **Unprocessable Entity** | Validation failed (e.g. missing fields, invalid referral code, insufficient points). |
| `500` | **Internal Server Error** | Server-side issue. |

