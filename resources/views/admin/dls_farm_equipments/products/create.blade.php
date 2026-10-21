@extends('adminlayouts.vertical', ['title' => 'Add Farm Equipment Product'])

@section('title', 'Add Farm Equipment Product')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Add Farm Equipment Product</h4>
            <p class="text-muted small mb-0">List new equipment with technical specifications, overview, features, 20% booking model, and gallery</p>
        </div>
        <a href="{{ route('admin.dls_farm_equipments.products.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back to Products
        </a>
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

    @if($categories->isEmpty())
        <div class="alert alert-warning rounded-3 mb-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <i class="bx bx-error-circle me-1 fs-18"></i>
                    <strong>No Farm Equipment Categories found.</strong> Please add a category first before creating products.
                </div>
                <a href="{{ route('admin.dls_farm_equipments.categories.index') }}" class="btn btn-warning btn-sm fw-semibold">
                    <i class="bx bx-plus me-1"></i> Add Category
                </a>
            </div>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="card border border-light-subtle shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">Equipment Details, Technical Specifications & Pricing</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.dls_farm_equipments.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Category, Subcategory & Title -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Category *</label>
                                <select name="category_id" id="category_select" class="form-select" required>
                                    <option value="" disabled selected>-- Select Farm Category --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Subcategory</label>
                                <select name="subcategory_id" id="subcategory_select" class="form-select">
                                    <option value="">-- No Subcategory / Select Category First --</option>
                                    @foreach($subcategories as $sub)
                                        <option value="{{ $sub->id }}" data-category="{{ $sub->category_id }}" {{ old('subcategory_id') == $sub->id ? 'selected' : '' }}>
                                            {{ $sub->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Product Name *</label>
                                <input type="text" name="name" class="form-control" required 
                                       placeholder="e.g. Solar Submersible Water Pump 5HP" value="{{ old('name') }}">
                            </div>
                        </div>

                        <!-- Model & SKU -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Model Code</label>
                                <input type="text" name="model_code" class="form-control" 
                                       placeholder="e.g. DLS-PUMP-500" value="{{ old('model_code') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">SKU Code</label>
                                <input type="text" name="sku" class="form-control" 
                                       placeholder="e.g. SKU-FARM-001" value="{{ old('sku') }}">
                            </div>
                        </div>

                        <!-- PRODUCT OVERVIEW -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark d-flex align-items-center gap-1">
                                <iconify-icon icon="solar:document-text-bold" class="text-primary fs-5"></iconify-icon>
                                Product Overview & Agricultural Use
                            </label>
                            <textarea name="overview" class="form-control" rows="4" 
                                      placeholder="Provide an overview of the agricultural equipment: crop utility, discharge volume, water efficiency, power requirement, borehole compatibility, and farmer benefits...">{{ old('overview') }}</textarea>
                            <span class="micro text-muted">Displayed prominently in product details on mobile app and catalog.</span>
                        </div>

                        <!-- KEY FEATURES & HIGHLIGHTS -->
                        <div class="card border border-light-subtle shadow-sm mb-4">
                            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:star-bold" class="text-warning fs-5"></iconify-icon>
                                        Key Features & Highlights
                                    </h6>
                                    <span class="micro text-muted">Bullet points showcasing key advantages, safety, subsidy support, and smart controllers</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success fw-semibold" id="addFeatureBtn">
                                    + Add Feature
                                </button>
                            </div>
                            <div class="card-body p-3">
                                <div id="featuresContainer">
                                    @php
                                        $initFarmFeatures = old('features', [
                                            '5HP 3-Phase Submersible DC Motor with Copper Winding',
                                            'MPPT Smart Solar Inverter / Controller Included with IP65 Enclosure',
                                            'Stainless Steel 304 High-Durability Impeller for Sandy Water',
                                            'Auto Dry-Run & Reverse Polarity Electronic Protection',
                                        ]);
                                    @endphp
                                    @foreach($initFarmFeatures as $feat)
                                        <div class="input-group mb-2 feature-row">
                                            <span class="input-group-text bg-light text-muted">
                                                <iconify-icon icon="solar:check-circle-bold" class="text-success"></iconify-icon>
                                            </span>
                                            <input type="text" name="features[]" class="form-control" 
                                                   placeholder="Feature bullet point (e.g. 5HP MPPT Smart Solar Inverter)" 
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
                                    <span class="micro text-muted">Engineered agricultural parameters (e.g. Power Rating, Discharge Capacity, Max Head Depth, Solar Array, Working Depth)</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" id="addSpecBtn">
                                    + Add Specification
                                </button>
                            </div>
                            <div class="card-body p-3">
                                <div id="specsContainer">
                                    @php
                                        $initFarmSpecs = old('specs', [
                                            'Power Rating'          => '5 HP (3.7 kW)',
                                            'Discharge Capacity'    => '25,000 Liters/Hour',
                                            'Max Head Depth'        => '120 meters',
                                            'Solar Array Required'  => '4800W - 6000W DC',
                                            'Operating Voltage'     => '180V - 450V DC',
                                            'Outlet Diameter'       => '2.5 Inches (65 mm)',
                                            'Borehole Minimum Size' => '4 Inches (100 mm)',
                                        ]);
                                    @endphp
                                    @foreach($initFarmSpecs as $sName => $sVal)
                                        <div class="row g-2 mb-2 spec-row align-items-center">
                                            <div class="col-md-5">
                                                <input type="text" name="spec_names[]" class="form-control" 
                                                       placeholder="Parameter Name (e.g. Power Rating)" value="{{ $sName }}">
                                            </div>
                                            <div class="col-md-6">
                                                <input type="text" name="spec_values[]" class="form-control" 
                                                       placeholder="Value (e.g. 5 HP (3.7 kW))" value="{{ $sVal }}">
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

                        <!-- PRODUCT IMAGES & GALLERY -->
                        <div class="card border border-light-subtle shadow-sm mb-4">
                            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:gallery-wide-bold" class="text-primary fs-5"></iconify-icon>
                                        Equipment Images & Gallery
                                    </h6>
                                    <span class="micro text-muted">Upload primary machine showcase image and multiple equipment gallery photos</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary fw-semibold d-flex align-items-center gap-1" id="addGalleryBtnTop">
                                    <iconify-icon icon="solar:add-circle-bold" class="fs-6"></iconify-icon>
                                    + Add Gallery Image
                                </button>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <!-- Main Equipment Image -->
                                    <div class="col-lg-5 col-md-12">
                                        <div class="p-3 bg-light rounded border border-light-subtle h-100">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label fw-semibold text-dark mb-0">Main Machine Image <span class="text-danger">*</span></label>
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Primary Showcase</span>
                                            </div>
                                            <span class="micro text-muted d-block mb-3">Upload JPG, PNG or WEBP (Max 4MB). Featured in catalog cards & hero slider.</span>
                                            
                                            <div class="mb-3">
                                                <input type="file" name="main_image" id="main_image_input" class="form-control" accept="image/*">
                                            </div>

                                            <!-- Live Preview Container for Main Image -->
                                            <div id="mainImagePreviewWrapper" class="d-none">
                                                <div class="position-relative d-inline-block border rounded p-1 bg-white shadow-sm">
                                                    <img id="mainImagePreview" src="" alt="Main Preview" class="rounded" style="max-height: 140px; max-width: 100%; object-fit: contain;">
                                                    <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 p-0 rounded-circle d-flex align-items-center justify-content-center" 
                                                            id="clearMainImageBtn" style="width: 22px; height: 22px;" title="Remove image">
                                                        <iconify-icon icon="solar:close-circle-bold" class="fs-6"></iconify-icon>
                                                    </button>
                                                </div>
                                                <div class="micro text-muted mt-1" id="mainImageInfo"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional Gallery Images with + Options -->
                                    <div class="col-lg-7 col-md-12">
                                        <div class="p-3 bg-light rounded border border-light-subtle h-100 d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <label class="form-label fw-semibold text-dark mb-0">Equipment Gallery Images</label>
                                                    <span class="micro text-muted d-block">Add multiple angles (engine, attachment, side, cabin, blades, etc.)</span>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary fw-semibold d-flex align-items-center gap-1" id="addGalleryBtnHeader">
                                                    <iconify-icon icon="solar:add-circle-bold" class="fs-6"></iconify-icon>
                                                    + Add Image
                                                </button>
                                            </div>

                                            <!-- Dynamic Gallery Rows Container -->
                                            <div id="galleryContainer" class="d-flex flex-column gap-2 flex-grow-1">
                                                <!-- Dynamic rows will be inserted here -->
                                            </div>

                                            <!-- Gallery Footer with Count & Add More Button -->
                                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top border-light-subtle">
                                                <span class="micro text-muted" id="galleryCountBadge">1 photo slot ready</span>
                                                <button type="button" class="btn btn-sm btn-primary fw-semibold d-flex align-items-center gap-1" id="addGalleryBtnBottom">
                                                    <iconify-icon icon="solar:add-circle-bold" class="fs-6"></iconify-icon>
                                                    + Add Another Image
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing and 20% Calculation -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Total Price (MRP ₹) *</label>
                                <input type="number" step="0.01" name="mrp" id="mrpInput" class="form-control" 
                                       required placeholder="e.g. 125000" value="{{ old('mrp') }}">
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
                                <span class="micro text-muted">Remaining 80% balance due on delivery</span>
                            </div>
                        </div>

                        <!-- Stock & Promotional Offer -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Stock Units *</label>
                                <input type="number" name="stock" class="form-control" value="{{ old('stock', 15) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Demonstration Video URL</label>
                                <input type="url" name="video_url" class="form-control" 
                                       placeholder="https://youtube.com/watch?v=..." value="{{ old('video_url') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Promotional / Offer Text</label>
                                <input type="text" name="offer_text" class="form-control" 
                                       placeholder="e.g. Govt Subsidy Assistance Available" value="{{ old('offer_text') }}">
                            </div>
                        </div>

                        <!-- Self Dealer & Referral Settings -->
                        <div class="card bg-light border-0 mb-4 p-3">
                            <h6 class="fw-bold text-dark mb-2">Referral & Self Dealer Eligibility</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Eligible Referral Calculation Value (₹)</label>
                                    <input type="number" step="0.01" name="eligible_referral_value" class="form-control" 
                                           placeholder="Leave empty to use MRP automatically" value="{{ old('eligible_referral_value') }}">
                                    <span class="micro text-muted">Base value for referral commission calculations.</span>
                                </div>
                                <div class="col-md-6 d-flex flex-column justify-content-center gap-2 pt-2">
                                    <div class="form-check">
                                        <input type="checkbox" name="referral_eligible" value="1" class="form-check-input" id="refEligible" checked>
                                        <label class="form-check-label fw-semibold text-dark small" for="refEligible">
                                            Referral Eligible (Enables points on customer referral)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" name="self_dealer_eligible" value="1" class="form-check-input" id="sdEligible" checked>
                                        <label class="form-check-label fw-semibold text-dark small" for="sdEligible">
                                            Self Dealer Activator (Eligible for self-dealer margin)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Warranty, Installation & Delivery Info -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Warranty Information</label>
                                <input type="text" name="warranty_info" class="form-control" 
                                       value="{{ old('warranty_info', '5 Years Manufacturer Warranty on Pump & Controller') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Installation & Demo Info</label>
                                <input type="text" name="installation_info" class="form-control" 
                                       value="{{ old('installation_info', 'Free On-site Farm Demo, Assembly & Testing') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark">Delivery Handling Info</label>
                                <input type="text" name="delivery_info" class="form-control" 
                                       value="{{ old('delivery_info', 'Delivered by Local Authorised Delivery & Service Partner (DSP)') }}">
                            </div>
                        </div>

                        <!-- Featured Deal Switch -->
                        <div class="card border border-warning-subtle bg-warning-subtle p-3 rounded-3 mb-4">
                            <div class="form-check form-switch mb-1">
                                <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="featCheck">
                                <label class="form-check-label fw-bold text-dark fs-14" for="featCheck">
                                    🔥 Mark as Featured Farm Equipment
                                </label>
                            </div>
                            <span class="text-muted small">
                                Featured items are spotlighted on promotional banners and top search results.
                            </span>
                        </div>

                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="bx bx-save me-1"></i> Save Equipment & Specifications
                        </button>
                        <a href="{{ route('admin.dls_farm_equipments.products.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
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
                <input type="text" name="features[]" class="form-control" placeholder="Feature bullet point (e.g. MPPT Controller with Auto Dry-Run Protection)">
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
                    <input type="text" name="spec_names[]" class="form-control" placeholder="Parameter (e.g. Max Head Depth)">
                </div>
                <div class="col-md-6">
                    <input type="text" name="spec_values[]" class="form-control" placeholder="Value (e.g. 120 meters)">
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

    // Dynamic Subcategory Loader based on selected Farm Category
    const categorySelect = document.getElementById('category_select');
    const subcategorySelect = document.getElementById('subcategory_select');

    if (categorySelect && subcategorySelect) {
        categorySelect.addEventListener('change', function() {
            const catId = this.value;
            subcategorySelect.innerHTML = '<option value="">Loading subcategories...</option>';

            if (!catId) {
                subcategorySelect.innerHTML = '<option value="">-- No Subcategory / Select Category First --</option>';
                return;
            }

            fetch(`{{ route('admin.dls_farm_equipments.subcategories.by-category') }}?category_id=${catId}`)
                .then(res => res.json())
                .then(data => {
                    subcategorySelect.innerHTML = '<option value="">-- None / General Category --</option>';
                    if (data.status && data.subcategories && data.subcategories.length > 0) {
                        data.subcategories.forEach(sub => {
                            const opt = document.createElement('option');
                            opt.value = sub.id;
                            opt.textContent = sub.name;
                            subcategorySelect.appendChild(opt);
                        });
                    }
                })
                .catch(err => {
                    console.error('Error fetching subcategories:', err);
                    subcategorySelect.innerHTML = '<option value="">-- None / General Category --</option>';
                });
        });
    }

    // Dynamic Product Gallery with + Add Image
    const galleryContainer = document.getElementById('galleryContainer');
    const addGalleryBtnTop = document.getElementById('addGalleryBtnTop');
    const addGalleryBtnHeader = document.getElementById('addGalleryBtnHeader');
    const addGalleryBtnBottom = document.getElementById('addGalleryBtnBottom');
    const galleryCountBadge = document.getElementById('galleryCountBadge');
    let galleryRowIndex = 0;

    function formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    function updateGalleryStats() {
        if (!galleryContainer) return;
        const rows = galleryContainer.querySelectorAll('.gallery-row');
        let filledCount = 0;
        rows.forEach(r => {
            const input = r.querySelector('.gallery-file-input');
            if (input && input.files && input.files.length > 0) {
                filledCount += input.files.length;
            }
        });
        if (galleryCountBadge) {
            if (filledCount === 0) {
                galleryCountBadge.textContent = `${rows.length} photo slot(s) ready (no photos selected)`;
            } else {
                galleryCountBadge.innerHTML = `<span class="text-success fw-bold">${filledCount} photo(s) selected</span> across ${rows.length} slot(s)`;
            }
        }
    }

    function createGalleryRow(openFileDialog = false) {
        if (!galleryContainer) return;
        galleryRowIndex++;
        const rowId = `gallery_row_${galleryRowIndex}`;
        const div = document.createElement('div');
        div.className = 'gallery-row p-2 bg-white rounded border border-light-subtle shadow-sm';
        div.id = rowId;
        div.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <div class="gallery-thumb-box rounded border bg-light d-flex align-items-center justify-content-center overflow-hidden position-relative" 
                         style="width: 54px; height: 54px; min-width: 54px;">
                        <iconify-icon icon="solar:gallery-wide-outline" class="text-muted fs-4 placeholder-icon"></iconify-icon>
                        <img src="" class="preview-img w-100 h-100 d-none" style="object-fit: cover;" alt="Gallery preview">
                    </div>
                </div>
                <div class="col">
                    <div class="input-group input-group-sm">
                        <input type="file" name="gallery[]" class="form-control gallery-file-input" accept="image/*" multiple>
                        <button type="button" class="btn btn-outline-danger remove-gallery-btn" title="Remove this photo slot">
                            <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-1 px-1">
                        <span class="micro text-muted file-info-text text-truncate" style="max-width: 250px;">Select photo (or multiple)</span>
                        <span class="badge bg-light text-muted border border-light-subtle file-size-badge d-none"></span>
                    </div>
                </div>
            </div>
        `;

        galleryContainer.appendChild(div);

        const fileInput = div.querySelector('.gallery-file-input');
        const previewImg = div.querySelector('.preview-img');
        const placeholderIcon = div.querySelector('.placeholder-icon');
        const fileInfoText = div.querySelector('.file-info-text');
        const fileSizeBadge = div.querySelector('.file-size-badge');

        fileInput.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                const firstFile = this.files[0];
                const totalFiles = this.files.length;

                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('d-none');
                    placeholderIcon.classList.add('d-none');
                };
                reader.readAsDataURL(firstFile);

                if (totalFiles === 1) {
                    fileInfoText.textContent = firstFile.name;
                    fileSizeBadge.textContent = formatFileSize(firstFile.size);
                    fileSizeBadge.classList.remove('d-none');
                } else {
                    fileInfoText.innerHTML = `<strong>${firstFile.name}</strong> + ${totalFiles - 1} more`;
                    let totalSize = Array.from(this.files).reduce((acc, f) => acc + f.size, 0);
                    fileSizeBadge.textContent = `${totalFiles} files (${formatFileSize(totalSize)})`;
                    fileSizeBadge.classList.remove('d-none');
                }
            } else {
                previewImg.src = '';
                previewImg.classList.add('d-none');
                placeholderIcon.classList.remove('d-none');
                fileInfoText.textContent = 'Select photo (or multiple)';
                fileSizeBadge.classList.add('d-none');
            }
            updateGalleryStats();
        });

        updateGalleryStats();

        if (openFileDialog) {
            fileInput.click();
        }
    }

    if (galleryContainer) {
        createGalleryRow(false);

        if (addGalleryBtnTop) {
            addGalleryBtnTop.addEventListener('click', function () {
                createGalleryRow(true);
            });
        }

        if (addGalleryBtnHeader) {
            addGalleryBtnHeader.addEventListener('click', function () {
                createGalleryRow(true);
            });
        }

        if (addGalleryBtnBottom) {
            addGalleryBtnBottom.addEventListener('click', function () {
                createGalleryRow(true);
            });
        }

        document.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.remove-gallery-btn');
            if (removeBtn && galleryContainer.contains(removeBtn)) {
                const row = removeBtn.closest('.gallery-row');
                const totalRows = galleryContainer.querySelectorAll('.gallery-row').length;
                if (totalRows > 1) {
                    row.remove();
                } else {
                    const fileInput = row.querySelector('.gallery-file-input');
                    const previewImg = row.querySelector('.preview-img');
                    const placeholderIcon = row.querySelector('.placeholder-icon');
                    const fileInfoText = row.querySelector('.file-info-text');
                    const fileSizeBadge = row.querySelector('.file-size-badge');

                    if (fileInput) fileInput.value = '';
                    if (previewImg) {
                        previewImg.src = '';
                        previewImg.classList.add('d-none');
                    }
                    if (placeholderIcon) placeholderIcon.classList.remove('d-none');
                    if (fileInfoText) fileInfoText.textContent = 'Select photo (or multiple)';
                    if (fileSizeBadge) fileSizeBadge.classList.add('d-none');
                }
                updateGalleryStats();
            }
        });
    }

    // Main Image Instant Preview
    const mainImageInput = document.getElementById('main_image_input');
    const mainImagePreviewWrapper = document.getElementById('mainImagePreviewWrapper');
    const mainImagePreview = document.getElementById('mainImagePreview');
    const mainImageInfo = document.getElementById('mainImageInfo');
    const clearMainImageBtn = document.getElementById('clearMainImageBtn');

    if (mainImageInput && mainImagePreviewWrapper && mainImagePreview) {
        mainImageInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const reader = new FileReader();
                reader.onload = function (e) {
                    mainImagePreview.src = e.target.result;
                    mainImagePreviewWrapper.classList.remove('d-none');
                    if (mainImageInfo) {
                        mainImageInfo.textContent = `${file.name} (${formatFileSize(file.size)})`;
                    }
                };
                reader.readAsDataURL(file);
            } else {
                mainImagePreviewWrapper.classList.add('d-none');
                mainImagePreview.src = '';
            }
        });

        if (clearMainImageBtn) {
            clearMainImageBtn.addEventListener('click', function () {
                mainImageInput.value = '';
                mainImagePreviewWrapper.classList.add('d-none');
                mainImagePreview.src = '';
            });
        }
    }

    // Form submit protection: disable empty file inputs before submit
    const productForm = document.querySelector('form[action*="products"]');
    if (productForm) {
        productForm.addEventListener('submit', function () {
            document.querySelectorAll('.gallery-file-input').forEach(input => {
                if (!input.files || input.files.length === 0) {
                    input.disabled = true;
                }
            });
        });
    }

    window.addEventListener('pageshow', function () {
        document.querySelectorAll('.gallery-file-input').forEach(input => {
            input.disabled = false;
        });
    });
});
</script>
@endsection
