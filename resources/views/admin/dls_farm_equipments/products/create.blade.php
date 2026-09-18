@extends('adminlayouts.vertical', ['title' => 'Add Farm Equipment Product'])

@section('title', 'Add Farm Equipment Product')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Add Farm Equipment Product</h4>
            <p class="text-muted small mb-0">List new equipment with 20% booking model, specs, and gallery</p>
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
                    <h6 class="fw-bold text-dark mb-0">Equipment Specifications & Pricing</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.dls_farm_equipments.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Category & Title -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label class="form-label fw-semibold text-dark">Category *</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="" disabled selected>-- Select Farm Category --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-7">
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

                        <!-- Image Uploads -->
                        <div class="row g-3 mb-3 p-3 bg-light rounded border border-light-subtle">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Main Product Image</label>
                                <input type="file" name="main_image" class="form-control" accept="image/*">
                                <span class="micro text-muted">Upload JPG, PNG or WEBP (Max 4MB)</span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Gallery Images</label>
                                <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                                <span class="micro text-muted">Select multiple images for machine gallery</span>
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
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Stock Units *</label>
                                <input type="number" name="stock" class="form-control" value="{{ old('stock', 15) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Promotional / Offer Text</label>
                                <input type="text" name="offer_text" class="form-control" 
                                       placeholder="e.g. Subsidy Available / Free Demo" value="{{ old('offer_text') }}">
                            </div>
                        </div>

                        <!-- Video URL -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Demonstration Video URL</label>
                            <input type="url" name="video_url" class="form-control" 
                                   placeholder="https://youtube.com/watch?v=..." value="{{ old('video_url') }}">
                        </div>

                        <!-- Self Dealer & Referral Settings -->
                        <div class="card bg-light border-0 mb-3 p-3">
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

                        <!-- Warranty & Installation Info -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Warranty Information</label>
                                <input type="text" name="warranty_info" class="form-control" 
                                       value="{{ old('warranty_info', '2 Years Comprehensive Manufacturer Warranty') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark">Installation & Demo Info</label>
                                <input type="text" name="installation_info" class="form-control" 
                                       value="{{ old('installation_info', 'Free On-site Farm Demo & Installation') }}">
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
                            <i class="bx bx-save me-1"></i> Save Product & Upload Images
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
});
</script>
@endsection
