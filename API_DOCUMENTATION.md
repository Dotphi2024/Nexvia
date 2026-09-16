# NEXVIA API Documentation

> **Base URL**: `http://127.0.0.1:8000` (Local) / `https://your-domain.com` (Production)  
> **API Version**: `v1` / `Current`  
> **Headers**: `Accept: application/json`, `Content-Type: application/json`  
> **Authentication**: `Authorization: Bearer <api_token>` or `token=<api_token>` (query/body/header) or `id=<customer_id>`

---

## Table of Contents

1. [Authentication & Profile](#1-authentication--profile)
2. [Categories & Banners](#2-categories--banners)
3. [Products & Search](#3-products--search)
4. [Cart & Wishlist](#4-cart--wishlist)
5. [User Addresses](#5-user-addresses)
6. [Checkout & Online Payments](#6-checkout--online-payments)
7. [Flexi-Bookings & Balance Payments](#7-flexi-bookings--balance-payments)
8. [Referral & Product Credit Wallet (New & Enhanced)](#8-referral--product-credit-wallet)
9. [Self-Dealer Ecosystem & 5-Stage Cycle](#9-self-dealer-ecosystem--5-stage-cycle)
10. [Order Delivery Tracking](#10-order-delivery-tracking)
11. [Warranties, Service Tickets & Installations](#11-warranties-service-tickets--installations)

---

## 1. Authentication & Profile

### 1.1 Register Customer
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
  "referral_code": "NEXAB12CD"
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Customer registered successfully.",
  "data": {
    "id": 42,
    "name": "Rahul Sharma",
    "phone": "9876543210",
    "email": "rahul@example.com",
    "api_token": "a1b2c3d4e5f6...",
    "referral_code": "NEX-987654",
    "referral_url": "http://127.0.0.1:8000/ref/NEX-987654",
    "is_self_dealer": false,
    "self_dealer_status": "inactive",
    "wallet_balance": 0.00
  }
}
```

---

### 1.2 Customer Login
* **Method**: `POST`
* **URL**: `/api/auth/login` (or `/api/customer/login`)
* **Auth**: Public

#### Request Body
```json
{
  "phone": "9876543210",
  "password": "Password@123"
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Login successful.",
  "data": {
    "token": "a1b2c3d4e5f6...",
    "user": {
      "id": 42,
      "name": "Rahul Sharma",
      "phone": "9876543210",
      "email": "rahul@example.com",
      "is_self_dealer": true,
      "self_dealer_status": "active",
      "referral_code": "NEX-987654",
      "wallet_balance": 15000.00
    }
  }
}
```

---

### 1.3 Send OTP
* **Method**: `POST`
* **URL**: `/api/auth/send-otp` (or `/api/customer/send-otp`)
* **Auth**: Public

#### Request Body
```json
{
  "phone": "9876543210"
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "OTP sent successfully to 9876543210.",
  "otp_debug": "123456"
}
```

---

### 1.4 Verify OTP
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

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Phone verified successfully.",
  "data": {
    "token": "a1b2c3d4e5f6...",
    "user_id": 42
  }
}
```

---

### 1.5 Get Customer Profile
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

### 1.6 Update Profile
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
* **URL**: `/api/products/{idOrSlug}`
* **Auth**: Public

#### Response (`200 OK`)
```json
{
  "status": true,
  "data": {
    "id": 1,
    "name": "NEXVIA 55-inch Ultra HD 4K Smart LED TV",
    "mrp": 50000.0,
    "booking_down_payment": 10000.0,
    "balance_amount": 40000.0,
    "referral_benefit": {
      "is_eligible": true,
      "activation_credit_percentage": 20.0,
      "estimated_activation_points": 10000.0
    }
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

## 7. Flexi-Bookings & Balance Payments

### 7.1 List Customer Bookings
* **Method**: `GET`
* **URL**: `/api/customer/bookings` (or `/api/bookings`)
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
      "booking_date": "2026-09-10",
      "balance_due_date": "2026-11-09",
      "payment_status": "partially_paid",
      "booking_status": "confirmed",
      "days_remaining": 54
    }
  ]
}
```

---

### 7.2 Create New Flexi-Booking (Token Down Payment)
* **Method**: `POST`
* **URL**: `/api/customer/bookings` (or `/api/bookings`)
* **Auth**: Required (`customer.auth`)

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
    }
  }
}
```

---

### 7.3 Pay Remaining Balance (80%)
* **Method**: `POST`
* **URL**: `/api/customer/bookings/{id}/pay-balance`
* **Auth**: Required (`customer.auth`)

> **Auto-Approval**: When the booking becomes `fully_paid`, any pending referral incentive points and activation credits tied to this booking are **automatically approved** and moved to the available wallet balance. (The manual **Qualify** button in Admin remains available for overrides).
>
> **Stake Enforcement**: If `use_product_credit` is `true`, wallet points are deducted. If the deduction dips into your 20% activation credit stake, Self-Dealer status is automatically cancelled!

#### Request Body
```json
{
  "use_product_credit": true
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Balance payment completed successfully. 1 pending credit/referral reward(s) have been auto-approved to available balance.",
  "data": {
    "booking_number": "BK-20260916-7788",
    "credit_applied": 15000.0,
    "cash_paid": 25000.0,
    "balance_amount": 0.0,
    "payment_status": "fully_paid",
    "referrals_auto_approved": 1,
    "wallet_balance_remain": 0.0,
    "self_dealer_cancelled": false,
    "self_dealer_status": "active",
    "is_self_dealer": true
  }
}
```

---

### 7.4 Cancel Booking
* **Method**: `POST`
* **URL**: `/api/customer/bookings/{id}/cancel` (or `/api/bookings/{id}/cancel`)
* **Auth**: Required (`customer.auth`)

> **Actions Executed on Cancellation**:
> 1. Marks booking status as `cancelled` with reason and timestamp `cancelled_at`.
> 2. **Reverses referral credits**: Any pending or available referral/activation points tied to this booking are automatically reversed with audit history.
> 3. **Revokes Self-Dealer status**: If this was the customer's activation booking (`activation_booking_id`), their Self-Dealer status is automatically revoked (`is_self_dealer = false`, `self_dealer_status = 'cancelled'`).
> 4. **Refunds redeemed credits**: Any NEXVIA Product Credits that were redeemed towards this booking or balance are automatically refunded back to the user's wallet.

#### Request Body
```json
{
  "reason": "Changed my mind / opted for alternate model"
}
```

#### Response (`200 OK`)
```json
{
  "status": true,
  "message": "Booking BK-20260916-7788 has been cancelled successfully. Your Self-Dealer status has been revoked because this was your activation booking.",
  "data": {
    "booking_id": 88,
    "booking_number": "BK-20260916-7788",
    "booking_status": "cancelled",
    "cancellation_reason": "Changed my mind / opted for alternate model",
    "cancelled_at": "2026-09-16T12:55:00+05:30",
    "referrals_reversed": 1,
    "self_dealer_revoked": true,
    "self_dealer_status": "cancelled",
    "is_self_dealer": false,
    "credits_refunded": 0.0,
    "current_wallet_balance": 0.0
  }
}
```

---

### 7.5 Transfer Booking Ownership
* `POST /api/customer/bookings/{id}/transfer` — Request transfer (`to_name`, `to_phone`)
* `POST /api/customer/bookings/{id}/transfer/confirm` — Confirm OTP for transfer

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

## Summary of Error Status Codes

| Code | Status | Meaning |
| :--- | :--- | :--- |
| `200` | **OK** | Request succeeded. |
| `401` | **Unauthenticated** | Missing or invalid API token / Customer ID. |
| `403` | **Forbidden** | Account inactive or insufficient privileges. |
| `404` | **Not Found** | Requested resource (Product, Booking, User) does not exist. |
| `422` | **Unprocessable Entity** | Validation failed (e.g. missing fields, invalid referral code, insufficient points). |
| `500` | **Internal Server Error** | Server-side issue. |
