@extends('adminlayouts.vertical')

@section('title', 'Add Offline Customer Purchase')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.bookings.index') }}" class="text-muted text-decoration-none small">
                    <iconify-icon icon="solar:arrow-left-linear" class="align-middle"></iconify-icon> Bookings
                </a>
                <span class="text-muted small">/</span>
                <span class="text-dark small fw-semibold">New Offline Purchase</span>
            </div>
            <h4 class="fw-extrabold text-dark mb-1">Record Offline Customer Purchase</h4>
            <p class="text-muted small mb-0">Record counter sales, showroom bookings, or direct bank transfer orders. Automatically sets up digital receipt, 60-day balance schedule, and DSP delivery tracking.</p>
        </div>
        <div>
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline-secondary px-3 py-2 rounded-3">
                Cancel
            </a>
        </div>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2 mb-2">
                <iconify-icon icon="solar:danger-triangle-bold" class="fs-18 text-danger"></iconify-icon>
                <strong>Please correct the following errors:</strong>
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.bookings.store') }}" method="POST" enctype="multipart/form-data" id="offlineBookingForm">
        @csrf

        <div class="row g-4">
            <!-- Left 8 Columns: Form Fields -->
            <div class="col-lg-8">

                <!-- 1. Customer Information Section -->
                <div class="card border-0 rounded-4 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white rounded-pill px-2 py-1 fs-12 fw-bold">1</span>
                            <h5 class="card-title mb-0 fw-bold text-dark">Customer Information</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <!-- Customer Type Switch -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-2">Customer Profile Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check form-check-inline border rounded-3 p-3 flex-fill bg-light" id="optNewWrap">
                                    <input class="form-check-input" type="radio" name="customer_type" id="typeNew" value="new" {{ old('customer_type', 'new') === 'new' ? 'checked' : '' }} onchange="toggleCustomerType()">
                                    <label class="form-check-label fw-bold text-dark ms-1" for="typeNew">
                                        <iconify-icon icon="solar:user-plus-bold" class="text-primary me-1 align-middle"></iconify-icon>
                                        New Customer / Walk-in Buyer
                                        <span class="d-block text-muted micro fw-normal mt-1">Creates an active customer account automatically</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-inline border rounded-3 p-3 flex-fill bg-light" id="optExistingWrap">
                                    <input class="form-check-input" type="radio" name="customer_type" id="typeExisting" value="existing" {{ old('customer_type') === 'existing' ? 'checked' : '' }} onchange="toggleCustomerType()">
                                    <label class="form-check-label fw-bold text-dark ms-1" for="typeExisting">
                                        <iconify-icon icon="solar:user-check-bold" class="text-success me-1 align-middle"></iconify-icon>
                                        Existing Registered Customer
                                        <span class="d-block text-muted micro fw-normal mt-1">Select from registered users in the database</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Existing Customer Selector Dropdown -->
                        <div id="existingCustomerBox" class="mb-4 {{ old('customer_type') === 'existing' ? '' : 'd-none' }}">
                            <label class="form-label fw-bold text-dark">Select Existing Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customerIdSelect" class="form-select bg-light" onchange="onExistingCustomerSelect()">
                                <option value="">-- Choose Customer by Name or Phone --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}"
                                        data-name="{{ $c->name }}"
                                        data-phone="{{ $c->phone }}"
                                        data-email="{{ $c->email }}"
                                        data-address="{{ $c->address }}"
                                        data-pincode="{{ $c->pincode }}"
                                        data-city="{{ $c->city }}"
                                        data-state="{{ $c->state }}"
                                        {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->name }} ({{ $c->phone }}) - {{ $c->city ?? 'No City' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Customer Details Inputs -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Customer Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" id="customer_name" class="form-control" placeholder="e.g. Ramesh Kumar" value="{{ old('customer_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Mobile Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" name="customer_phone" id="customer_phone" class="form-control" placeholder="10-digit mobile number" value="{{ old('customer_phone') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Email Address <span class="text-muted micro">(Optional)</span></label>
                                <input type="email" name="customer_email" id="customer_email" class="form-control" placeholder="customer@example.com" value="{{ old('customer_email') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Referrer / Self-Dealer Code <span class="text-muted micro">(Optional)</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted">
                                        <iconify-icon icon="solar:gift-bold"></iconify-icon>
                                    </span>
                                    <input type="text" name="referrer_code" id="referrer_code" class="form-control text-uppercase" placeholder="e.g. NEX-AB1234 or DLR-1002" value="{{ old('referrer_code') }}">
                                </div>
                                <span class="micro text-muted">Enter code if customer was introduced by an authorized self dealer</span>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark">Delivery / Billing Address <span class="text-danger">*</span></label>
                                <textarea name="shipping_address" id="shipping_address" rows="2" class="form-control" placeholder="House/Flat No, Street, Landmark" required>{{ old('shipping_address') }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark">Pincode <span class="text-danger">*</span></label>
                                <input type="text" name="pincode" id="pincode" class="form-control" placeholder="6-digit Pincode" value="{{ old('pincode') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark">City <span class="text-danger">*</span></label>
                                <input type="text" name="city" id="city" class="form-control" placeholder="City" value="{{ old('city') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark">State <span class="text-danger">*</span></label>
                                <input type="text" name="state" id="state" class="form-control" placeholder="State" value="{{ old('state') }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Product & Quantity Selection Section -->
                <div class="card border-0 rounded-4 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white rounded-pill px-2 py-1 fs-12 fw-bold">2</span>
                            <h5 class="card-title mb-0 fw-bold text-dark">Product Selection & Pricing</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-bold text-dark mb-0">Select Product <span class="text-danger">*</span></label>
                                    <span class="micro text-muted">Use search bar below to quickly find any product</span>
                                </div>

                                <!-- Product Search Bar with Live Suggestions Dropdown -->
                                <div class="position-relative mb-2">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-primary">
                                            <iconify-icon icon="solar:magnifer-bold" class="fs-16"></iconify-icon>
                                        </span>
                                        <input type="text" id="productSearchInput" class="form-control border-start-0 ps-0" placeholder="Type to search product name, model code, or price..." autocomplete="off">
                                        <button class="btn btn-outline-secondary d-none" type="button" id="clearProductSearchBtn" onclick="clearProductSearch()" title="Clear Search">
                                            <iconify-icon icon="solar:close-circle-bold" class="fs-16"></iconify-icon>
                                        </button>
                                    </div>

                                    <!-- Floating Suggestions Dropdown List -->
                                    <div id="productSuggestionsPanel" class="position-absolute w-100 bg-white border rounded-3 shadow-lg p-2 d-none" style="z-index: 1050; max-height: 280px; overflow-y: auto; top: 100%; margin-top: 4px;">
                                        <div id="suggestionsList" class="d-flex flex-column gap-1"></div>
                                    </div>
                                </div>

                                <!-- Native Dropdown (also dynamically filtered as user types) -->
                                <select name="product_id" id="productIdSelect" class="form-select" required onchange="onProductSelect()">
                                    <option value="">-- Choose Product from Catalog ({{ count($products) }} items) --</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}"
                                            data-name="{{ $p->name }}"
                                            data-mrp="{{ $p->mrp }}"
                                            data-booking-pct="{{ $p->booking_percentage ?: 20 }}"
                                            data-booking-amount="{{ $p->booking_amount }}"
                                            data-balance-amount="{{ $p->balance_amount }}"
                                            data-model="{{ $p->model_code }}"
                                            data-eligible="{{ $p->self_dealer_eligible ? '1' : '0' }}"
                                            {{ old('product_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }} — ₹{{ number_format($p->mrp, 2) }} {{ $p->model_code ? '(' . $p->model_code . ')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div id="productCountFeedback" class="micro text-muted mt-1">Showing all {{ count($products) }} products in catalog</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark">Unit Price / MRP (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="mrp" id="unit_mrp" class="form-control fw-bold text-primary" value="{{ old('mrp', 0) }}" required oninput="recalculateTotals()">
                                <span class="micro text-muted">Auto-filled from catalog, editable for custom discount</span>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantity" class="form-control text-center fw-bold" value="{{ old('quantity', 1) }}" min="1" max="100" required oninput="recalculateTotals()">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Color / Variant <span class="text-muted micro">(Optional)</span></label>
                                <input type="text" name="selected_color" id="selected_color" class="form-control" placeholder="e.g. Red, Blue, Standard" value="{{ old('selected_color') }}">
                            </div>
                        </div>

                        <!-- Product info alert banner -->
                        <div id="productInfoBanner" class="alert alert-light border rounded-3 p-3 mt-3 d-none">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <strong class="text-dark d-block fs-14" id="bannerProductName">Product Name</strong>
                                    <span class="micro text-muted">Model: <span id="bannerModelCode" class="font-monospace fw-bold"></span></span>
                                </div>
                                <div class="text-end">
                                    <span class="text-muted micro d-block">Standard 20% Advance:</span>
                                    <strong class="text-success fs-14" id="bannerBookingAmt">₹0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Offline Payment & Booking Terms Section -->
                <div class="card border-0 rounded-4 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white rounded-pill px-2 py-1 fs-12 fw-bold">3</span>
                            <h5 class="card-title mb-0 fw-bold text-dark">Payment Details & Schedule</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Purchase Channel <span class="text-danger">*</span></label>
                                <select name="purchase_channel" class="form-select bg-light" required>
                                    <option value="offline_store" {{ old('purchase_channel') === 'offline_store' ? 'selected' : '' }}>Offline Store / Showroom Counter</option>
                                    <option value="offline_direct" {{ old('purchase_channel') === 'offline_direct' ? 'selected' : '' }}>Direct Dealership Sale</option>
                                    <option value="telephonic" {{ old('purchase_channel') === 'telephonic' ? 'selected' : '' }}>Phone / WhatsApp Booking</option>
                                    <option value="field_exhibition" {{ old('purchase_channel') === 'field_exhibition' ? 'selected' : '' }}>Agricultural Expo / Field Demo</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Payment Plan <span class="text-danger">*</span></label>
                                <select name="payment_type" id="payment_type" class="form-select" required onchange="onPaymentTypeChange()">
                                    <option value="booking_20" {{ old('payment_type', 'booking_20') === 'booking_20' ? 'selected' : '' }}>
                                        20% Booking Advance (80% in 60 Days)
                                    </option>
                                    <option value="full_payment" {{ old('payment_type') === 'full_payment' ? 'selected' : '' }}>
                                        Full 100% Upfront Payment
                                    </option>
                                    <option value="custom" {{ old('payment_type') === 'custom' ? 'selected' : '' }}>
                                        Custom Advance / Partial Amount
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark">Payment Method <span class="text-danger">*</span></label>
                                <select name="offline_payment_method" class="form-select" required>
                                    <option value="cash" {{ old('offline_payment_method') === 'cash' ? 'selected' : '' }}>💵 Cash In Hand</option>
                                    <option value="bank_transfer" {{ old('offline_payment_method') === 'bank_transfer' ? 'selected' : '' }}>🏦 Bank Transfer (NEFT/RTGS/IMPS)</option>
                                    <option value="upi" {{ old('offline_payment_method', 'upi') === 'upi' ? 'selected' : '' }}>📱 UPI / QR Code / NetBanking</option>
                                    <option value="cheque" {{ old('offline_payment_method') === 'cheque' ? 'selected' : '' }}>📑 Cheque / Demand Draft</option>
                                    <option value="pos_card" {{ old('offline_payment_method') === 'pos_card' ? 'selected' : '' }}>💳 POS Card Swipe (Debit/Credit)</option>
                                    <option value="other" {{ old('offline_payment_method') === 'other' ? 'selected' : '' }}>Other Offline Mode</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">UTR / Cheque No / Reference</label>
                                <input type="text" name="offline_payment_ref" class="form-control font-monospace" placeholder="e.g. UTR9284102 or CHQ-00124" value="{{ old('offline_payment_ref') }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark">Amount Paid Now (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="booking_amount" id="booking_amount" class="form-control fw-bold text-success" value="{{ old('booking_amount', 0) }}" required oninput="recalculateTotals(true)">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark">Remaining Balance Due (₹)</label>
                                <input type="number" step="0.01" name="balance_amount" id="balance_amount" class="form-control fw-bold text-danger bg-light" value="{{ old('balance_amount', 0) }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark">Booking Date <span class="text-danger">*</span></label>
                                <input type="date" name="booking_date" id="booking_date" class="form-control" value="{{ old('booking_date', date('Y-m-d')) }}" required onchange="updateDueDate()">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark">60-Day Balance Due Date</label>
                                <input type="date" name="balance_due_date" id="balance_due_date" class="form-control" value="{{ old('balance_due_date', date('Y-m-d', strtotime('+60 days'))) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Payment Status <span class="text-danger">*</span></label>
                                <select name="payment_status" id="payment_status" class="form-select" required>
                                    <option value="paid" {{ old('payment_status', 'paid') === 'paid' ? 'selected' : '' }}>20% Advance Paid (Balance Due)</option>
                                    <option value="fully_paid" {{ old('payment_status') === 'fully_paid' ? 'selected' : '' }}>Fully Paid</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Booking / Delivery Stage <span class="text-danger">*</span></label>
                                <select name="booking_status" id="booking_status" class="form-select" required>
                                    <option value="booked" {{ old('booking_status', 'booked') === 'booked' ? 'selected' : '' }}>Booked (Ready for Dispatch)</option>
                                    <option value="balance_paid" {{ old('booking_status') === 'balance_paid' ? 'selected' : '' }}>Balance Paid</option>
                                    <option value="completed" {{ old('booking_status') === 'completed' ? 'selected' : '' }}>Completed / Delivered Directly Over Counter</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark">Attach Receipt / Invoice / Deposit Slip <span class="text-muted micro">(PDF, JPG, PNG, Max 5MB)</span></label>
                                <input type="file" name="payment_receipt" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. DSP & Internal Notes Section -->
                <div class="card border-0 rounded-4 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary text-white rounded-pill px-2 py-1 fs-12 fw-bold">4</span>
                            <h5 class="card-title mb-0 fw-bold text-dark">DSP Partner & Counter Remarks</h5>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold text-dark">Assign Authorized DSP Partner</label>
                                <select name="dsp_id" class="form-select">
                                    <option value="">-- Auto-Match Nearest DSP (Based on Customer Pincode/State) --</option>
                                    @foreach($dsps as $dsp)
                                        <option value="{{ $dsp->id }}" {{ old('dsp_id') == $dsp->id ? 'selected' : '' }}>
                                            {{ $dsp->business_name ?? $dsp->applicant_name }} ({{ $dsp->applicant_name }}) - {{ $dsp->district }}, {{ $dsp->state }} (PIN: {{ $dsp->premises_pincode ?? $dsp->residential_pincode }})
                                        </option>
                                    @endforeach
                                </select>
                                <span class="micro text-muted">A delivery tracking number (TRK-XXXX) will be generated automatically for this partner and customer.</span>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold text-dark">Offline Counter Notes / Staff Remarks</label>
                                <textarea name="offline_notes" rows="2" class="form-control" placeholder="Staff salesperson name, physical receipt book number, counter location, delivery specifics...">{{ old('offline_notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 4 Columns: Interactive Live Order Summary Card -->
            <div class="col-lg-4">
                <div class="card border-0 rounded-4 shadow-sm sticky-top" style="top: 90px;">
                    <div class="card-header bg-primary text-white py-3 px-4 rounded-top-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0 fw-bold text-white">Purchase Summary</h5>
                            <span class="badge bg-white text-primary fw-bold micro">OFFLINE SALE</span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center pb-3 border-bottom">
                            <span class="text-muted small">Customer:</span>
                            <strong class="text-dark small" id="sumCustomerName">—</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted small">Product:</span>
                            <span class="text-dark small fw-semibold text-truncate ms-2" style="max-width: 180px;" id="sumProductName">Not selected</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted small">Quantity:</span>
                            <strong class="text-dark small" id="sumQuantity">1</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted small">Total MRP / Price:</span>
                            <strong class="text-dark fs-15" id="sumTotalMrp">₹0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom bg-success-subtle p-2 rounded-3 my-2">
                            <span class="text-success small fw-bold">Amount Paid Now:</span>
                            <strong class="text-success fs-15" id="sumPaidAmount">₹0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom bg-danger-subtle p-2 rounded-3 my-2">
                            <span class="text-danger small fw-bold">Balance in 60 Days:</span>
                            <strong class="text-danger fs-15" id="sumBalanceAmount">₹0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-muted small">60-Day Due Date:</span>
                            <strong class="text-primary small" id="sumDueDate">{{ date('d M, Y', strtotime('+60 days')) }}</strong>
                        </div>

                        <!-- System Automated Features Pill -->
                        <div class="bg-light p-3 rounded-3 mt-3">
                            <span class="micro fw-bold text-uppercase text-muted d-block mb-2">Automated on Submit:</span>
                            <div class="d-flex align-items-center gap-2 mb-1 micro text-dark">
                                <iconify-icon icon="solar:check-circle-bold" class="text-success fs-14"></iconify-icon>
                                <span>Official Digital Receipt generated</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-1 micro text-dark">
                                <iconify-icon icon="solar:check-circle-bold" class="text-success fs-14"></iconify-icon>
                                <span>Delivery Tracking (TRK-XXXX) initialized</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-1 micro text-dark">
                                <iconify-icon icon="solar:check-circle-bold" class="text-success fs-14"></iconify-icon>
                                <span>Referral commission linked if referred</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 micro text-dark">
                                <iconify-icon icon="solar:check-circle-bold" class="text-success fs-14"></iconify-icon>
                                <span>Self-Dealer status activated if product eligible</span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold fs-15 shadow-sm d-flex justify-content-center align-items-center gap-2">
                                <iconify-icon icon="solar:diskette-bold" class="fs-18"></iconify-icon>
                                <span>Create & Confirm Offline Entry</span>
                            </button>
                            <a href="{{ route('admin.bookings.index') }}" class="btn btn-link text-muted w-100 mt-2 text-decoration-none small text-center">
                                Back to Bookings List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function toggleCustomerType() {
    const isExisting = document.getElementById('typeExisting').checked;
    const existingBox = document.getElementById('existingCustomerBox');
    if (isExisting) {
        existingBox.classList.remove('d-none');
    } else {
        existingBox.classList.add('d-none');
    }
}

function onExistingCustomerSelect() {
    const select = document.getElementById('customerIdSelect');
    const selectedOption = select.options[select.selectedIndex];
    if (!selectedOption || !selectedOption.value) return;

    document.getElementById('customer_name').value = selectedOption.dataset.name || '';
    document.getElementById('customer_phone').value = selectedOption.dataset.phone || '';
    document.getElementById('customer_email').value = selectedOption.dataset.email || '';
    document.getElementById('shipping_address').value = selectedOption.dataset.address || '';
    document.getElementById('pincode').value = selectedOption.dataset.pincode || '';
    document.getElementById('city').value = selectedOption.dataset.city || '';
    document.getElementById('state').value = selectedOption.dataset.state || '';

    document.getElementById('sumCustomerName').textContent = selectedOption.dataset.name || '—';
}

document.getElementById('customer_name').addEventListener('input', function() {
    document.getElementById('sumCustomerName').textContent = this.value || '—';
});

// Catalog products cache for instant search
const catalogProducts = [
    @foreach($products as $p)
    {
        id: "{{ $p->id }}",
        name: {!! json_encode($p->name) !!},
        model: {!! json_encode($p->model_code ?? '') !!},
        mrp: {{ (float)$p->mrp }},
        bookingAmt: {{ (float)$p->booking_amount }},
        eligible: {{ $p->self_dealer_eligible ? 1 : 0 }}
    },
    @endforeach
];

const searchInput = document.getElementById('productSearchInput');
const suggestionsPanel = document.getElementById('productSuggestionsPanel');
const suggestionsList = document.getElementById('suggestionsList');
const clearBtn = document.getElementById('clearProductSearchBtn');
const nativeSelect = document.getElementById('productIdSelect');
const countFeedback = document.getElementById('productCountFeedback');

function filterProducts(query) {
    const q = (query || '').toLowerCase().trim();
    if (q.length > 0) {
        clearBtn.classList.remove('d-none');
    } else {
        clearBtn.classList.add('d-none');
    }

    const matches = catalogProducts.filter(p => {
        return p.name.toLowerCase().includes(q) ||
               p.model.toLowerCase().includes(q) ||
               p.mrp.toString().includes(q);
    });

    // 1. Render suggestions dropdown panel
    suggestionsList.innerHTML = '';
    if (matches.length === 0) {
        suggestionsList.innerHTML = `<div class="p-3 text-center text-muted small"><iconify-icon icon="solar:magnifer-outline" class="fs-18 mb-1"></iconify-icon><br>No products match "<strong>${escapeHtml(query)}</strong>"</div>`;
    } else {
        matches.forEach(p => {
            const item = document.createElement('div');
            item.className = 'd-flex justify-content-between align-items-center p-2 rounded-2 border-bottom cursor-pointer hover-bg-light';
            item.style.cursor = 'pointer';
            item.innerHTML = `
                <div>
                    <strong class="d-block fs-13 text-dark">${highlightMatch(p.name, q)}</strong>
                    <div class="d-flex align-items-center gap-1 mt-1">
                        ${p.model ? `<span class="badge bg-light text-dark font-monospace micro border">${p.model}</span>` : ''}
                        ${p.eligible ? `<span class="badge bg-success-subtle text-success micro">Self Dealer Eligible</span>` : ''}
                    </div>
                </div>
                <div class="text-end ps-2">
                    <span class="fw-bold text-primary fs-13 d-block">₹${p.mrp.toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>
                    <span class="micro text-success">20% Adv: ₹${p.bookingAmt.toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>
                </div>
            `;
            item.addEventListener('mouseenter', () => item.classList.add('bg-light'));
            item.addEventListener('mouseleave', () => item.classList.remove('bg-light'));
            item.addEventListener('click', () => {
                selectProductById(p.id, p.name);
            });
            suggestionsList.appendChild(item);
        });
    }

    if (q.length > 0) {
        suggestionsPanel.classList.remove('d-none');
    }

    // 2. Filter native select options
    Array.from(nativeSelect.options).forEach((opt, idx) => {
        if (idx === 0) return;
        const text = opt.textContent.toLowerCase();
        if (q === '' || text.includes(q)) {
            opt.hidden = false;
        } else {
            opt.hidden = true;
        }
    });

    if (q.length > 0) {
        countFeedback.textContent = `Found ${matches.length} matching product(s) for "${query}"`;
    } else {
        countFeedback.textContent = `Showing all ${catalogProducts.length} products in catalog`;
    }
}

function selectProductById(id, name) {
    nativeSelect.value = id;
    searchInput.value = name;
    clearBtn.classList.remove('d-none');
    suggestionsPanel.classList.add('d-none');
    onProductSelect();
}

function clearProductSearch() {
    searchInput.value = '';
    clearBtn.classList.add('d-none');
    suggestionsPanel.classList.add('d-none');
    filterProducts('');
    countFeedback.textContent = `Showing all ${catalogProducts.length} products in catalog`;
}

function highlightMatch(text, query) {
    if (!query) return escapeHtml(text);
    const escaped = escapeHtml(text);
    const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return escaped.replace(regex, '<mark class="bg-warning-subtle px-1 py-0 rounded">$1</mark>');
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

if (searchInput) {
    searchInput.addEventListener('input', function() {
        filterProducts(this.value);
    });

    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length > 0 || catalogProducts.length > 0) {
            filterProducts(this.value);
            suggestionsPanel.classList.remove('d-none');
        }
    });
}

// Close suggestions panel on click outside
document.addEventListener('click', function(e) {
    if (searchInput && suggestionsPanel && !searchInput.contains(e.target) && !suggestionsPanel.contains(e.target)) {
        suggestionsPanel.classList.add('d-none');
    }
});

// Escape key to close
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && suggestionsPanel) {
        suggestionsPanel.classList.add('d-none');
    }
});

function onProductSelect() {
    const select = document.getElementById('productIdSelect');
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) {
        document.getElementById('productInfoBanner').classList.add('d-none');
        document.getElementById('sumProductName').textContent = 'Not selected';
        return;
    }

    if (searchInput && (!searchInput.value || searchInput.value !== opt.dataset.name)) {
        searchInput.value = opt.dataset.name || opt.text;
        clearBtn.classList.remove('d-none');
    }

    const mrp = parseFloat(opt.dataset.mrp) || 0;
    const model = opt.dataset.model || 'N/A';
    const bookingAmt = parseFloat(opt.dataset.bookingAmount) || (mrp * 0.2);

    document.getElementById('unit_mrp').value = mrp.toFixed(2);
    document.getElementById('bannerProductName').textContent = opt.dataset.name;
    document.getElementById('bannerModelCode').textContent = model;
    document.getElementById('bannerBookingAmt').textContent = '₹' + bookingAmt.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('productInfoBanner').classList.remove('d-none');
    document.getElementById('sumProductName').textContent = opt.dataset.name;

    recalculateTotals();
}

function onPaymentTypeChange() {
    recalculateTotals();
}

function recalculateTotals(isCustomInput = false) {
    const unitMrp = parseFloat(document.getElementById('unit_mrp').value) || 0;
    const qty = parseInt(document.getElementById('quantity').value) || 1;
    const totalMrp = Math.round(unitMrp * qty * 100) / 100;

    const paymentType = document.getElementById('payment_type').value;
    let paidAmount = 0;

    if (!isCustomInput) {
        if (paymentType === 'full_payment') {
            paidAmount = totalMrp;
        } else if (paymentType === 'booking_20') {
            paidAmount = Math.round(totalMrp * 0.20 * 100) / 100;
        } else {
            paidAmount = parseFloat(document.getElementById('booking_amount').value) || 0;
        }
        document.getElementById('booking_amount').value = paidAmount.toFixed(2);
    } else {
        paidAmount = parseFloat(document.getElementById('booking_amount').value) || 0;
    }

    const balance = Math.max(0, Math.round((totalMrp - paidAmount) * 100) / 100);
    document.getElementById('balance_amount').value = balance.toFixed(2);

    // Auto-update Payment Status
    const statusSelect = document.getElementById('payment_status');
    if (balance <= 0) {
        statusSelect.value = 'fully_paid';
    } else {
        statusSelect.value = 'paid';
    }

    // Update Summary Card
    document.getElementById('sumQuantity').textContent = qty;
    document.getElementById('sumTotalMrp').textContent = '₹' + totalMrp.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('sumPaidAmount').textContent = '₹' + paidAmount.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('sumBalanceAmount').textContent = '₹' + balance.toLocaleString('en-IN', {minimumFractionDigits: 2});
}

function updateDueDate() {
    const bookingDateVal = document.getElementById('booking_date').value;
    if (bookingDateVal) {
        const d = new Date(bookingDateVal);
        d.setDate(d.getDate() + 60);
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        document.getElementById('balance_due_date').value = `${y}-${m}-${day}`;

        const options = { day: 'numeric', month: 'short', year: 'numeric' };
        document.getElementById('sumDueDate').textContent = d.toLocaleDateString('en-GB', options);
    }
}

// Initial calculation on load if values exist
document.addEventListener('DOMContentLoaded', function() {
    toggleCustomerType();
    if (document.getElementById('productIdSelect').value) {
        onProductSelect();
    }
    updateDueDate();
});
</script>
@endpush
