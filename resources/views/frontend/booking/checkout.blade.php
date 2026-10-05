@extends('frontend.layouts.app')

@section('title', 'Checkout Booking – ' . $product->name)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h3 class="fw-bold text-dark mb-4">Complete Your Booking</h3>

            <form action="{{ \Illuminate\Support\Facades\Route::has('booking.process') ? route('booking.process', $product->slug) : url('/api/bookings') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="payment_type" value="{{ $paymentType }}">

                <div class="row g-4">
                    <!-- Shipping / Customer Details Form -->
                    <div class="col-lg-7">
                        <div class="card card-nexvia p-4 mb-4">
                            <h5 class="fw-bold text-dark mb-3">Customer & Delivery Information</h5>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Full Name *</label>
                                <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $user->name ?? '') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Mobile Phone Number *</label>
                                <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', $user->phone ?? '') }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Delivery Address *</label>
                                <textarea name="shipping_address" class="form-control" rows="3" required placeholder="House/Flat No., Street, Landmark">{{ old('shipping_address') }}</textarea>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Pincode *</label>
                                    <input type="text" name="pincode" id="checkoutPincode" class="form-control font-monospace" value="{{ old('pincode', $user->pincode ?? '') }}" required onchange="lookupDsps(this.value)">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">City *</label>
                                    <input type="text" name="city" id="checkoutCity" class="form-control" value="{{ old('city', $user->city ?? '') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">State *</label>
                                    <input type="text" name="state" id="checkoutState" class="form-control" value="{{ old('state', $user->state ?? '') }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- AUTHORISED DELIVERY & SERVICE PARTNER (DSP) SELECTION -->
                        <div class="card card-nexvia p-4 mb-4 border-primary border-opacity-50">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="fs-4 text-primary"></iconify-icon>
                                <h6 class="fw-bold text-dark mb-0">Authorised Delivery & Service Partner (DSP)</h6>
                            </div>
                            <p class="small text-muted mb-3">
                                Select your preferred local territory partner for doorstep delivery, PDI check, and official handover:
                            </p>

                            <div id="dspSelectionContainer">
                                @if(!empty($availableDsps) && $availableDsps->count() > 0)
                                    <div class="row g-2">
                                        @foreach($availableDsps as $dsp)
                                            <div class="col-12">
                                                <label class="p-3 border rounded-3 d-flex justify-content-between align-items-center bg-light w-100 mb-0" style="cursor: pointer;">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <input class="form-check-input mt-0" type="radio" name="dsp_id" value="{{ $dsp->id }}" {{ $loop->first ? 'checked' : '' }}>
                                                        <div>
                                                            <strong class="text-dark d-block small">{{ $dsp->business_name ?: $dsp->applicant_name }}</strong>
                                                            <span class="micro text-muted">
                                                                <iconify-icon icon="solar:map-point-bold" class="align-middle"></iconify-icon>
                                                                {{ $dsp->district ?: 'Territory Hub' }} • PIN: {{ $dsp->premises_pincode ?: 'Serviced Area' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <span class="badge bg-primary text-white micro">
                                                        {{ $dsp->match_label ?? 'Authorised Partner' }}
                                                    </span>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="alert alert-secondary small mb-0 p-3 rounded-3 d-flex align-items-center gap-2">
                                        <iconify-icon icon="solar:info-circle-bold" class="fs-5 text-secondary"></iconify-icon>
                                        <div>Our system will automatically assign the nearest Authorised Delivery Partner for your territory.</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- PRODUCT CREDIT WALLET REDEMPTION -->
                        @if(Auth::guard('web')->check() && $user->wallet_balance > 0)
                            <div class="card card-nexvia p-4 mb-4 border-success bg-success bg-opacity-10">
                                <div class="d-flex align-items-center gap-3">
                                    <iconify-icon icon="solar:wallet-money-bold" class="fs-1 text-success"></iconify-icon>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0">Use Product Credit Wallet Balance</h6>
                                        <span class="small text-muted">Available Credit Balance: <strong class="text-success fs-6">₹{{ number_format($user->wallet_balance, 2) }}</strong></span>
                                    </div>
                                </div>
                                <hr class="my-2 border-success border-opacity-25">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="use_wallet" id="walletCheck" value="1">
                                    <label class="form-check-label fw-bold text-dark small" for="walletCheck">
                                        Apply Product Credit Wallet balance to discount your upfront payment!
                                    </label>
                                </div>
                            </div>
                        @endif

                        <!-- REFUND POLICY MANDATORY ACCEPTANCE -->
                        @if($paymentType === 'booking_20')
                            <div class="card card-nexvia p-4 border-warning bg-warning bg-opacity-10">
                                <h6 class="fw-bold text-dark mb-2">
                                    <iconify-icon icon="solar:shield-warning-bold" class="text-warning fs-5 align-middle me-1"></iconify-icon>
                                    Non-Refundable Booking Policy Declaration
                                </h6>
                                <p class="small text-secondary mb-3">
                                    By booking with NEXVIA, you pay only 20% today. The remaining 80% balance is payable within 60 days. Please note: <strong>The 20% Booking Amount is Non-Refundable</strong>, but your digital receipt is 100% <strong>Transferable</strong> to any other buyer.
                                </p>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="non_refundable_terms" id="termsCheck" required value="1">
                                    <label class="form-check-label fw-bold text-dark small" for="termsCheck">
                                        I understand and accept that the 20% Booking Amount is Non-Refundable, and the 80% balance is due within 60 days. *
                                    </label>
                                </div>
                                @error('non_refundable_terms')
                                    <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif
                    </div>

                    <!-- Order Summary Card -->
                    <div class="col-lg-5">
                        <div class="card card-nexvia p-4">
                            <h5 class="fw-bold text-dark mb-3">Order & Payment Summary</h5>

                            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-3">
                                <iconify-icon icon="{{ $product->category->icon ?? 'solar:box-bold-duotone' }}" style="font-size: 40px; color: #2563eb;"></iconify-icon>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">{{ $product->name }}</h6>
                                    <span class="small text-muted">{{ $product->category->name }}</span>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Product Price (MRP):</span>
                                <span class="fw-bold">₹{{ number_format($product->mrp, 2) }}</span>
                            </div>

                            <hr class="my-2">

                            @if($paymentType === 'booking_20')
                                <div class="d-flex justify-content-between mb-2 text-primary fs-5 fw-bold">
                                    <span>Payable Today (20%):</span>
                                    <span>₹{{ number_format($product->booking_amount, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3 text-secondary small">
                                    <span>Balance Due (within 60 Days):</span>
                                    <span class="fw-bold">₹{{ number_format($product->balance_amount, 2) }}</span>
                                </div>
                                <div class="p-2 bg-light rounded text-center small text-muted mb-4">
                                    Balance due date: <strong>{{ \Carbon\Carbon::today()->addDays(60)->format('d M, Y') }}</strong>
                                </div>
                            @else
                                <div class="d-flex justify-content-between mb-3 text-primary fs-5 fw-bold">
                                    <span>Full Payment Today:</span>
                                    <span>₹{{ number_format($product->mrp, 2) }}</span>
                                </div>
                            @endif

                            <!-- PAYMENT METHOD SELECTION -->
                            <h6 class="fw-bold text-dark mb-2">Select Payment Method</h6>
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="p-3 border rounded-3 d-flex align-items-center gap-2 w-100 bg-white shadow-sm border-primary" style="cursor: pointer;" id="optOnlineLabel">
                                        <input type="radio" name="payment_channel" value="online" checked class="form-check-input mt-0" onchange="togglePaymentMode('online')">
                                        <div>
                                            <strong class="d-block text-dark small">Razorpay Online</strong>
                                            <span class="micro text-muted">UPI / Cards / NetBanking / EMI</span>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <label class="p-3 border rounded-3 d-flex align-items-center gap-2 w-100 bg-light" style="cursor: pointer;" id="optQrLabel">
                                        <input type="radio" name="payment_channel" value="qr" class="form-check-input mt-0" onchange="togglePaymentMode('qr')">
                                        <div>
                                            <strong class="d-block text-dark small">Scan UPI QR</strong>
                                            <span class="micro text-muted">Direct Bank / QR Transfer</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- RAZORPAY ONLINE INFO -->
                            <div id="razorpayOnlineBox" class="card p-3 border border-primary bg-primary bg-opacity-10 rounded-3 mb-3">
                                <div class="d-flex align-items-center gap-3">
                                    <iconify-icon icon="solar:shield-check-bold" class="fs-2 text-primary"></iconify-icon>
                                    <div>
                                        <div class="fw-bold text-dark small">Instant Secure Gateway (Razorpay)</div>
                                        <div class="micro text-muted">Pay securely using Google Pay, PhonePe, Paytm, Debit/Credit Card, Net Banking or PayLater.</div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2 mt-2 pt-2 border-top border-primary border-opacity-25">
                                    <span class="badge bg-success text-white micro">100% Encrypted</span>
                                    <span class="badge bg-primary text-white micro">Instant Booking Receipt</span>
                                </div>
                            </div>

                            <!-- UPI QR PAYMENT OPTION (Hidden by default unless QR chosen) -->
                            <div id="upiQrBox" class="card p-3 border rounded-3 bg-light mb-3 text-center d-none">
                                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                    <iconify-icon icon="solar:qr-code-bold" class="fs-4 text-primary"></iconify-icon>
                                    <span class="fw-bold text-dark small">Scan & Pay with any UPI App</span>
                                </div>
                                <div class="text-center mb-2">
                                    <img src="{{ asset('images/dls_payment_qr.png') }}" alt="UPI QR Code" class="img-fluid rounded border bg-white p-2 shadow-sm" style="max-height: 180px;">
                                </div>
                                <div class="micro text-muted">
                                    Scan with <strong>Google Pay, PhonePe, Paytm, BHIM</strong>
                                    <div class="font-monospace text-dark fw-bold mt-1">UPI ID: dlsagroin.09@idfcbank</div>
                                    <div class="micro text-muted">A/c: DLS AGRO INFRAVENTURE PVT LTD</div>
                                </div>

                                <div class="mt-3 text-start">
                                    <label class="form-label small fw-bold text-dark d-flex justify-content-between align-items-center mb-1">
                                        <span>
                                            <iconify-icon icon="solar:document-upload-bold" class="text-primary me-1 align-middle"></iconify-icon>
                                            Payment Receipt / Screenshot
                                        </span>
                                        <span class="badge bg-secondary-subtle text-secondary micro">Optional</span>
                                    </label>
                                    <input type="file" name="payment_receipt" class="form-control form-control-sm" accept="image/*,.pdf">
                                    <span class="micro text-muted mt-1 d-block">Upload transfer screenshot (PNG, JPG, PDF).</span>
                                </div>
                            </div>

                            <!-- Hidden Razorpay inputs populated on callback -->
                            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                            <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
                            <input type="hidden" name="razorpay_signature" id="razorpay_signature">

                            <button type="button" id="submitPayBtn" class="btn btn-nexvia-primary btn-lg w-100 py-3 shadow">
                                Pay Now ₹{{ number_format($paymentType === 'booking_20' ? $product->booking_amount : $product->mrp, 0) }}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function togglePaymentMode(mode) {
    const rzpBox = document.getElementById('razorpayOnlineBox');
    const qrBox = document.getElementById('upiQrBox');
    const optOnline = document.getElementById('optOnlineLabel');
    const optQr = document.getElementById('optQrLabel');

    if (mode === 'online') {
        rzpBox.classList.remove('d-none');
        qrBox.classList.add('d-none');
        optOnline.classList.add('border-primary', 'shadow-sm', 'bg-white');
        optOnline.classList.remove('bg-light');
        optQr.classList.remove('border-primary', 'shadow-sm', 'bg-white');
        optQr.classList.add('bg-light');
    } else {
        rzpBox.classList.add('d-none');
        qrBox.classList.remove('d-none');
        optQr.classList.add('border-primary', 'shadow-sm', 'bg-white');
        optQr.classList.remove('bg-light');
        optOnline.classList.remove('border-primary', 'shadow-sm', 'bg-white');
        optOnline.classList.add('bg-light');
    }
}

document.getElementById('submitPayBtn').addEventListener('click', function(e) {
    e.preventDefault();

    const form = this.closest('form');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const selectedChannel = document.querySelector('input[name="payment_channel"]:checked')?.value || 'online';

    if (selectedChannel === 'qr') {
        form.submit();
        return;
    }

    // Razorpay Online Flow
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Initializing Secure Payment...';

    const customerName = form.querySelector('input[name="customer_name"]').value;
    const customerPhone = form.querySelector('input[name="customer_phone"]').value;
    const amountPayable = "{{ $paymentType === 'booking_20' ? $product->booking_amount : $product->mrp }}";
    const productId = "{{ $product->id }}";
    const paymentType = "{{ $paymentType }}";

    fetch('/api/payments/create-order', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            amountPayable: amountPayable,
            productId: productId,
            paymentType: paymentType,
            name: customerName,
            phone: customerPhone
        })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.status || !data.order) {
            alert(data.message || 'Unable to create payment order. Please try again.');
            btn.disabled = false;
            btn.innerHTML = 'Pay Now ₹' + Number(amountPayable).toLocaleString('en-IN');
            return;
        }

        const options = {
            "key": data.gateway.key_id || "{{ config('services.razorpay.key_id', 'rzp_live_Tk75PpmJwnvItA') }}",
            "amount": data.order.amount,
            "currency": "INR",
            "name": "NEXVIA Mobility",
            "description": "{{ $paymentType === 'booking_20' ? '20% Token Booking' : 'Full Payment' }} for {{ $product->name }}",
            "order_id": data.order.id,
            "prefill": {
                "name": customerName,
                "contact": customerPhone
            },
            "theme": {
                "color": "#0D6EFD"
            },
            "handler": function (response) {
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                document.getElementById('razorpay_signature').value = response.razorpay_signature;

                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Confirming Booking...';
                form.submit();
            },
            "modal": {
                "ondismiss": function() {
                    btn.disabled = false;
                    btn.innerHTML = 'Pay Now ₹' + Number(amountPayable).toLocaleString('en-IN');
                }
            }
        };

        const rzp = new Razorpay(options);
        rzp.on('payment.failed', function (response){
            alert("Payment failed: " + response.error.description);
            btn.disabled = false;
            btn.innerHTML = 'Pay Now ₹' + Number(amountPayable).toLocaleString('en-IN');
        });
        rzp.open();
    })
    .catch(err => {
        console.error('Payment Error:', err);
        alert('Payment gateway initialization failed. Please try again.');
        btn.disabled = false;
        btn.innerHTML = 'Pay Now ₹' + Number(amountPayable).toLocaleString('en-IN');
    });
});

function lookupDsps(pincode) {
    if (!pincode || pincode.trim().length < 4) return;
    const container = document.getElementById('dspSelectionContainer');
    const city = document.getElementById('checkoutCity') ? document.getElementById('checkoutCity').value : '';
    const state = document.getElementById('checkoutState') ? document.getElementById('checkoutState').value : '';

    fetch(`/dsp/available-by-pincode?pincode=${encodeURIComponent(pincode)}&city=${encodeURIComponent(city)}&state=${encodeURIComponent(state)}`)
        .then(res => res.json())
        .then(data => {
            if (data.status && data.dsps && data.dsps.length > 0) {
                let html = '<div class="row g-2">';
                data.dsps.forEach((dsp, idx) => {
                    html += `
                        <div class="col-12">
                            <label class="p-3 border rounded-3 d-flex justify-content-between align-items-center bg-light w-100 mb-0" style="cursor: pointer;">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input mt-0" type="radio" name="dsp_id" value="${dsp.id}" ${idx === 0 ? 'checked' : ''}>
                                    <div>
                                        <strong class="text-dark d-block small">${dsp.business_name}</strong>
                                        <span class="micro text-muted">
                                            <iconify-icon icon="solar:map-point-bold" class="align-middle"></iconify-icon>
                                            ${dsp.district || 'Territory Hub'} • PIN: ${dsp.premises_pincode || pincode}
                                        </span>
                                    </div>
                                </div>
                                <span class="badge bg-primary text-white micro">
                                    ${dsp.match_label}
                                </span>
                            </label>
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            }
        })
        .catch(err => console.log('DSP lookup error:', err));
}
</script>
@endsection

