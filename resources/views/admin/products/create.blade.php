@extends('adminlayouts.vertical', ['title' => 'Add Product'])

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Add New Product</h4>
            <p class="text-muted small mb-0">Create product listing with technical specifications, overview, features, and 20% booking model</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">← Back to List</a>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border border-light-subtle shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">Product Details, Specifications & Media</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Category & Title -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold text-dark">Category *</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="" disabled selected>-- Select Category --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }} ({{ ucfirst(str_replace('_', ' ', $cat->type)) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label fw-semibold text-dark">Product Name *</label>
                                <input type="text" name="name" class="form-control" required 
                                       placeholder="e.g. NEXVIA E4 High-Speed Electric Scooter" value="{{ old('name') }}">
                            </div>
                        </div>

                        <!-- Model & SKU -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Model Code</label>
                                <input type="text" name="model_code" class="form-control" 
                                       placeholder="e.g. NEX-E4-2026" value="{{ old('model_code') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">SKU Code</label>
                                <input type="text" name="sku" class="form-control" 
                                       placeholder="e.g. SKU-NEX-001" value="{{ old('sku') }}">
                            </div>
                        </div>

                        <!-- PRODUCT OVERVIEW -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                <iconify-icon icon="solar:document-text-bold" class="text-primary fs-5"></iconify-icon>
                                Product Overview & Summary
                            </label>
                            <textarea name="overview" class="form-control" rows="4" 
                                      placeholder="Provide an overview of the product: core purpose, comfort, battery/performance highlights, ergonomics, build quality, and daily convenience...">{{ old('overview') }}</textarea>
                            <span class="micro text-muted">Displayed prominently in product details on mobile app and web frontend.</span>
                        </div>

                        <!-- KEY FEATURES & HIGHLIGHTS -->
                        <div class="card border border-light-subtle shadow-sm mb-4">
                            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:star-bold" class="text-warning fs-5"></iconify-icon>
                                        Key Features & Highlights
                                    </h6>
                                    <span class="micro text-muted">Bullet points showcasing key advantages, safety, battery, and smart connectivity</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success fw-semibold" id="addFeatureBtn">
                                    + Add Feature
                                </button>
                            </div>
                            <div class="card-body p-3">
                                <div id="featuresContainer">
                                    @php
                                        $initFeatures = old('features', [
                                            'High Efficiency BLDC Hub Motor with regenerative braking',
                                            'Fast Charging: 0 to 80% in 120 minutes with Smart BMS',
                                            'IP67 Water & Dust Resistance on Battery Pack',
                                        ]);
                                    @endphp
                                    @foreach($initFeatures as $feat)
                                        <div class="input-group mb-2 feature-row">
                                            <span class="input-group-text bg-light text-muted">
                                                <iconify-icon icon="solar:check-circle-bold" class="text-success"></iconify-icon>
                                            </span>
                                            <input type="text" name="features[]" class="form-control" 
                                                   placeholder="Feature bullet point (e.g. Smart Digital Color TFT Cluster with Turn-by-Turn GPS)" 
                                                   value="{{ $feat }}">
                                            <button type="button" class="btn btn-outline-danger remove-feature-btn" title="Remove Feature">
                                                <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- TECHNICAL SPECIFICATIONS -->
                        <div class="card border border-light-subtle shadow-sm mb-4">
                            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:tuning-square-2-bold" class="text-primary fs-5"></iconify-icon>
                                        Technical Specifications
                                    </h6>
                                    <span class="micro text-muted">Engineered parameters and values (e.g. Motor Power, Battery Capacity, Top Speed, Range, Weight, Brakes)</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" id="addSpecBtn">
                                    + Add Specification
                                </button>
                            </div>
                            <div class="card-body p-3">
                                <div id="specsContainer">
                                    @php
                                        $initSpecs = old('specs', [
                                            'Motor Type'        => 'High-Torque BLDC Hub Motor',
                                            'Motor Power'       => '2500 Watts Peak',
                                            'Battery Capacity'  => '3.2 kWh Lithium-Ion (Swappable)',
                                            'Riding Range'      => '120 km per full charge',
                                            'Top Speed'         => '65 km/h',
                                            'Braking System'    => 'Front & Rear Combined Disc Brakes (CBS)',
                                            'Charging Time'     => '3.5 Hours (Standard Home 15A Socket)',
                                            'Payload Capacity'  => '160 kg',
                                        ]);
                                    @endphp
                                    @foreach($initSpecs as $sName => $sVal)
                                        <div class="row g-2 mb-2 spec-row align-items-center">
                                            <div class="col-md-5">
                                                <input type="text" name="spec_names[]" class="form-control" 
                                                       placeholder="Parameter (e.g. Battery Capacity)" value="{{ $sName }}">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" name="spec_values[]" class="form-control" 
                                                       placeholder="Value (e.g. 3.2 kWh Lithium-Ion)" value="{{ $sVal }}">
                                            </div>
                                            <div class="col-md-1 text-end">
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-spec-btn w-100" title="Remove Spec">
                                                    <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- IMAGE UPLOADS -->
                        <div class="row g-3 mb-4 p-3 bg-light rounded border border-light-subtle">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Main Product Image *</label>
                                <input type="file" name="main_image" class="form-control" accept="image/*">
                                <span class="micro text-muted">Upload JPG, PNG or WEBP (Max 4MB)</span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Additional Gallery Images</label>
                                <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                                <span class="micro text-muted">Select multiple images for product gallery</span>
                            </div>
                        </div>

                        <!-- PRICING & 20% BOOKING -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Total Product Price (MRP ₹) *</label>
                                <input type="number" step="0.01" name="mrp" id="mrpInput" class="form-control" 
                                       required placeholder="e.g. 75000" value="{{ old('mrp') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Booking Percentage (%)</label>
                                <input type="number" name="booking_percentage" id="bookingPercentInput" 
                                       class="form-control bg-light" value="20" required readonly>
                                <span class="micro text-muted">20% Booking token</span>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Booking Token Amount (₹)</label>
                                <input type="text" id="calculatedBooking" class="form-control bg-light fw-bold text-success" 
                                       value="₹0.00" readonly>
                                <span class="micro text-muted">Remaining 80% due on delivery</span>
                            </div>
                        </div>

                        <!-- STOCK, VIDEO & OFFER -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Available Stock Count *</label>
                                <input type="number" name="stock" class="form-control" value="{{ old('stock', 25) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Product Video URL</label>
                                <input type="url" name="video_url" class="form-control" 
                                       placeholder="https://youtube.com/watch?v=..." value="{{ old('video_url') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Offer / Promotional Badge</label>
                                <input type="text" name="offer_text" class="form-control" 
                                       placeholder="e.g. Festival Special ₹2,000 Off" value="{{ old('offer_text') }}">
                            </div>
                        </div>

                        <!-- SELF DEALER & REFERRAL INCENTIVE SETTINGS -->
                        <div class="card bg-light border-0 mb-4 p-3">
                            <h6 class="fw-bold text-dark mb-2">Self Dealer & Referral Settings</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Eligible Referral Calculation Value (₹)</label>
                                    <input type="number" step="0.01" name="eligible_referral_value" class="form-control" 
                                           placeholder="Leave empty to use MRP automatically" value="{{ old('eligible_referral_value') }}">
                                    <span class="micro text-muted">Basis for referral points calculation. Defaults to MRP if blank.</span>
                                </div>
                                <div class="col-md-6 d-flex flex-column justify-content-center gap-2 pt-2">
                                    <div class="form-check">
                                        <input type="checkbox" name="referral_eligible" value="1" class="form-check-input" id="refEligible" checked>
                                        <label class="form-check-label fw-semibold text-dark small" for="refEligible">
                                            Referral Eligible (Can be referred to earn points)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="self_dealer_eligible" value="1" class="form-check-input" id="sdEligible" checked>
                                        <label class="form-check-label fw-semibold text-dark small" for="sdEligible">
                                            Self Dealer Activator (Booking this activates Self Dealer status)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- WARRANTY & INSTALLATION -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Warranty Information</label>
                                <input type="text" name="warranty_info" class="form-control" 
                                       value="{{ old('warranty_info', '3 Years Comprehensive Battery & Motor Warranty') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Installation & Doorstep Setup</label>
                                <input type="text" name="installation_info" class="form-control" 
                                       value="{{ old('installation_info', 'Free Doorstep Unboxing & Assembly Available') }}">
                            </div>
                        </div>

                        <!-- FEATURED DEAL SWITCH -->
                        <div class="card border border-warning-subtle bg-warning-subtle p-3 rounded-3 mb-4">
                            <div class="form-check form-switch mb-0">
                                <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="featCheck" checked>
                                <label class="form-check-label fw-bold text-dark fs-14" for="featCheck">
                                    🔥 Mark as Trending & Lightning Deal (Feature on App Home)
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary fw-semibold px-4">Save Product & Specifications</button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 20% calculation
    const mrpInput = document.getElementById('mrpInput');
    const calculatedBooking = document.getElementById('calculatedBooking');

    function calculate() {
        const mrp = parseFloat(mrpInput.value) || 0;
        const booking = mrp * 0.20;
        calculatedBooking.value = '₹' + booking.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    if (mrpInput) {
        mrpInput.addEventListener('input', calculate);
        calculate();
    }

    // Dynamic Features
    const featuresContainer = document.getElementById('featuresContainer');
    const addFeatureBtn = document.getElementById('addFeatureBtn');

    if (addFeatureBtn) {
        addFeatureBtn.addEventListener('click', function () {
            const div = document.createElement('div');
            div.className = 'input-group mb-2 feature-row';
            div.innerHTML = `
                <span class="input-group-text bg-light text-muted">
                    <iconify-icon icon="solar:check-circle-bold" class="text-success"></iconify-icon>
                </span>
                <input type="text" name="features[]" class="form-control" placeholder="Feature bullet point (e.g. Regenerative Braking System)">
                <button type="button" class="btn btn-outline-danger remove-feature-btn" title="Remove Feature">
                    <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                </button>
            `;
            featuresContainer.appendChild(div);
        });
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('.remove-feature-btn')) {
            const row = e.target.closest('.feature-row');
            if (document.querySelectorAll('.feature-row').length > 1) {
                row.remove();
            } else {
                row.querySelector('input').value = '';
            }
        }
    });

    // Dynamic Specifications
    const specsContainer = document.getElementById('specsContainer');
    const addSpecBtn = document.getElementById('addSpecBtn');

    if (addSpecBtn) {
        addSpecBtn.addEventListener('click', function () {
            const div = document.createElement('div');
            div.className = 'row g-2 mb-2 spec-row align-items-center';
            div.innerHTML = `
                <div class="col-md-5">
                    <input type="text" name="spec_names[]" class="form-control" placeholder="Parameter (e.g. Wheelbase)">
                </div>
                <div class="col-md-6">
                    <input type="text" name="spec_values[]" class="form-control" placeholder="Value (e.g. 1350 mm)">
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-spec-btn w-100" title="Remove Spec">
                        <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                    </button>
                </div>
            `;
            specsContainer.appendChild(div);
        });
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('.remove-spec-btn')) {
            const row = e.target.closest('.spec-row');
            if (document.querySelectorAll('.spec-row').length > 1) {
                row.remove();
            } else {
                row.querySelectorAll('input').forEach(i => i.value = '');
            }
        }
    });
});
</script>
@endsection
