@extends('adminlayouts.vertical', ['title' => 'Home Page Banners'])

@section('title', 'Home Page Banners Management')

@section('content')
<div class="container-fluid py-3">
    <!-- Header & Breadcrumbs -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Home Section Banners</h4>
            <p class="text-muted small mb-0">Manage sliders and promotional banners displayed exclusively on the <strong>Home Index Page</strong> of the mobile app & website.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <iconify-icon icon="solar:home-smile-outline" class="me-1"></iconify-icon> Dashboard
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <iconify-icon icon="solar:check-circle-bold" class="fs-20 me-2 text-success"></iconify-icon>
                <strong>Success!</strong> &nbsp; {{ session('success') }}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center mb-1">
                <iconify-icon icon="solar:danger-triangle-bold" class="fs-20 me-2 text-danger"></iconify-icon>
                <strong>Please correct the following errors:</strong>
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- 4 KPI Stat Cards for Home Section -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-12 text-uppercase fw-semibold">Total Home Banners</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ $totalBanners }}</h3>
                    </div>
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <iconify-icon icon="solar:home-smile-bold" class="fs-24"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-12 text-uppercase fw-semibold">Live on Home Page</span>
                        <h3 class="fw-bold text-success mb-0 mt-1">{{ $activeBanners }}</h3>
                    </div>
                    <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <iconify-icon icon="solar:check-circle-bold" class="fs-24"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-12 text-uppercase fw-semibold">Top Hero Sliders</span>
                        <h3 class="fw-bold text-warning mb-0 mt-1">{{ $heroBanners }}</h3>
                    </div>
                    <div class="rounded-circle bg-warning-subtle text-warning p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <iconify-icon icon="solar:slider-minimalistic-horizontal-bold" class="fs-24"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100 border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted fs-12 text-uppercase fw-semibold">Middle Promo Banners</span>
                        <h3 class="fw-bold text-info mb-0 mt-1">{{ $promoBanners }}</h3>
                    </div>
                    <div class="rounded-circle bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <iconify-icon icon="solar:tag-bold" class="fs-24"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Two-Column Layout -->
    <div class="row g-4">
        <!-- Add Home Banner Form (Left Column) -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border border-light-subtle shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:add-circle-bold" class="text-primary fs-18"></iconify-icon>
                        Add Home Page Banner
                    </h6>
                    <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1 fs-11">Home Screen</span>
                </div>
                <div class="card-body p-3">
                    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Banner Title -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark fs-13">Banner Heading / Title *</label>
                            <input type="text" name="title" class="form-control form-control-sm" required placeholder="e.g. Mega Smart LED TV Festival" value="{{ old('title') }}">
                        </div>

                        <!-- Subtitle / Tagline -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark fs-13">Subheading / Description</label>
                            <input type="text" name="subtitle" class="form-control form-control-sm" placeholder="e.g. Book now with 20% advance & earn rewards" value="{{ old('subtitle') }}">
                        </div>

                        <!-- Badge Text -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark fs-13">Offer Tag / Badge (Optional)</label>
                            <input type="text" name="badge_text" class="form-control form-control-sm" placeholder="e.g. 20% ADVANCE BOOKING or LIMITED TIME" value="{{ old('badge_text') }}">
                        </div>

                        <!-- Home Page Position -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-7">
                                <label class="form-label fw-semibold text-dark fs-13">Home Page Position *</label>
                                <select name="banner_type" class="form-select form-select-sm" required>
                                    <option value="hero" selected>Top Hero Slider (Carousel)</option>
                                    <option value="promo">Middle Promo Banner (Offer Strip)</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold text-dark fs-13">Slide Order</label>
                                <input type="number" name="sort_order" class="form-control form-control-sm" min="1" value="{{ old('sort_order', 1) }}" placeholder="1">
                            </div>
                        </div>

                        <!-- Target Action Type -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark fs-13">Click Action (When User Taps) *</label>
                            <select name="target_type" id="createTargetType" class="form-select form-select-sm" required onchange="toggleTargetFields('create')">
                                <option value="category">Open Product Category</option>
                                <option value="product">Open Specific Product</option>
                                <option value="booking">Open 20% Booking Flow</option>
                                <option value="url">External / Custom URL</option>
                                <option value="none">No Action (Informational)</option>
                            </select>
                        </div>

                        <!-- Category Selector -->
                        <div class="mb-3" id="createCategoryField">
                            <label class="form-label fw-semibold text-dark fs-13">Select Category</label>
                            <select name="target_id" id="createCategoryId" class="form-select form-select-sm">
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }} (Code: {{ $cat->referral_category_code ?: $cat->slug }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Product Selector -->
                        <div class="mb-3 d-none" id="createProductField">
                            <label class="form-label fw-semibold text-dark fs-13">Select Product</label>
                            <select id="createProductId" class="form-select form-select-sm" onchange="syncProductId('create')">
                                <option value="">-- Select Product --</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->name }} (₹{{ number_format($prod->mrp, 2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Custom URL field -->
                        <div class="mb-3" id="createUrlField">
                            <label class="form-label fw-semibold text-dark fs-13">Custom Link / Deep-link</label>
                            <input type="text" name="link_url" id="createLinkUrl" class="form-control form-control-sm" placeholder="e.g. /category/smart-led-tv or https://..." value="{{ old('link_url') }}">
                        </div>

                        <!-- Button Call to Action -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark fs-13">Button Label (CTA)</label>
                            <input type="text" name="button_text" class="form-control form-control-sm" placeholder="e.g. Book Now with 20% Advance" value="{{ old('button_text', 'Shop Now') }}">
                        </div>

                        <!-- Banner Image Upload -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark fs-13">Banner Image *</label>
                            <input type="file" name="image" id="createImageInput" class="form-control form-control-sm" accept="image/*" required onchange="previewBannerImage(this, 'createImagePreviewContainer')">
                            <div class="form-text fs-11 text-muted">Dimensions: 1200x500px for Top Slider, 1200x350px for Middle Banner (JPG, PNG, WEBP max 5MB).</div>
                            
                            <div id="createImagePreviewContainer" class="mt-2 text-center d-none p-2 border rounded bg-light">
                                <img id="createImagePreview" src="" alt="Banner Preview" class="img-fluid rounded" style="max-height: 120px; object-fit: cover;">
                            </div>
                        </div>

                        <!-- Active Toggle -->
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createIsActive" checked>
                            <label class="form-check-label fw-semibold text-dark fs-13" for="createIsActive">Display on Home Index Page (Active)</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                            <iconify-icon icon="solar:upload-track-2-bold" class="me-1"></iconify-icon> Publish to Home Page
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Banners List Table (Right Column) -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border border-light-subtle shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Active Home Section Banners</h6>
                        <span class="text-muted fs-12">Ordered by slide order on the Home Page index</span>
                    </div>

                    <!-- Filter Tabs for Home Positions -->
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.banners.index') }}" class="btn btn-sm {{ !request('type') ? 'btn-dark' : 'btn-outline-secondary' }} fs-11 px-2 py-1">All Home Banners</a>
                        <a href="{{ route('admin.banners.index', ['type' => 'hero']) }}" class="btn btn-sm {{ request('type') == 'hero' ? 'btn-primary' : 'btn-outline-secondary' }} fs-11 px-2 py-1">Top Sliders</a>
                        <a href="{{ route('admin.banners.index', ['type' => 'promo']) }}" class="btn btn-sm {{ request('type') == 'promo' ? 'btn-info text-white' : 'btn-outline-secondary' }} fs-11 px-2 py-1">Middle Banners</a>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th style="width: 130px;">Banner Image</th>
                                    <th>Heading & Tap Target</th>
                                    <th>Home Position</th>
                                    <th>Order</th>
                                    <th>Home Visibility</th>
                                    <th class="text-end" style="width: 130px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($banners as $index => $banner)
                                    <tr>
                                        <td class="text-muted fw-bold fs-12">{{ $index + 1 }}</td>
                                        <td>
                                            <div class="position-relative rounded overflow-hidden shadow-sm" style="width: 115px; height: 55px; background: #f1f5f9;">
                                                <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-100 h-100" style="object-fit: cover;" onerror="this.src='https://placehold.co/115x55?text=Home+Banner'">
                                                @if($banner->badge_text)
                                                    <span class="position-absolute bottom-0 start-0 bg-dark bg-opacity-75 text-white fs-10 px-1 text-truncate" style="max-width: 100%;">
                                                        {{ $banner->badge_text }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <h6 class="fw-bold text-dark mb-1 fs-13">{{ $banner->title }}</h6>
                                            @if($banner->subtitle)
                                                <p class="text-muted fs-11 mb-1 text-truncate" style="max-width: 260px;">{{ $banner->subtitle }}</p>
                                            @endif
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="badge bg-light text-secondary border fs-10">
                                                    Target: {{ ucfirst($banner->target_type) }}
                                                </span>
                                                @if($banner->target_type == 'category' && $banner->category)
                                                    <span class="badge bg-primary-subtle text-primary fs-10">
                                                        {{ $banner->category->name }}
                                                    </span>
                                                @elseif($banner->target_type == 'product' && $banner->product)
                                                    <span class="badge bg-info-subtle text-info fs-10 text-truncate" style="max-width: 150px;">
                                                        {{ $banner->product->name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($banner->banner_type == 'hero')
                                                <span class="badge bg-primary-subtle text-primary fw-semibold fs-11">
                                                    <iconify-icon icon="solar:slider-minimalistic-horizontal-bold" class="me-1"></iconify-icon> Top Hero Slider
                                                </span>
                                            @elseif($banner->banner_type == 'promo')
                                                <span class="badge bg-info-subtle text-info fw-semibold fs-11">
                                                    <iconify-icon icon="solar:tag-bold" class="me-1"></iconify-icon> Middle Promo Banner
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary fw-semibold fs-11">
                                                    {{ ucfirst($banner->banner_type) }}
                                                </span>
                                            @endif
                                            <div class="text-muted fs-10 mt-1">
                                                <iconify-icon icon="solar:cursor-bold" class="fs-10"></iconify-icon> {{ number_format($banner->clicks_count) }} taps
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border fw-bold fs-12 px-2 py-1">
                                                #{{ $banner->sort_order }}
                                            </span>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.banners.status', $banner->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm p-0 border-0" title="Click to toggle visibility on home page">
                                                    @if($banner->is_active)
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2 fs-11">
                                                            <iconify-icon icon="solar:check-circle-bold" class="me-1"></iconify-icon> Visible on Home
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-1 px-2 fs-11">
                                                            <iconify-icon icon="solar:close-circle-bold" class="me-1"></iconify-icon> Hidden
                                                        </span>
                                                    @endif
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end gap-1">
                                                <!-- Edit Button (Modal) -->
                                                <button type="button" class="btn btn-light btn-sm text-primary p-1 px-2" title="Edit Home Banner" onclick='openEditModal(@json($banner))'>
                                                    <iconify-icon icon="solar:pen-new-square-linear" class="fs-16"></iconify-icon>
                                                </button>

                                                <!-- Delete Button -->
                                                <form action="{{ route('admin.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this home banner?');" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-light btn-sm text-danger p-1 px-2" title="Delete Banner">
                                                        <iconify-icon icon="solar:trash-bin-trash-outline" class="fs-16"></iconify-icon>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted">
                                                <iconify-icon icon="solar:home-smile-outline" class="fs-48 text-muted opacity-50 mb-2"></iconify-icon>
                                                <h6 class="fw-bold text-dark">No Home Banners Found</h6>
                                                <p class="fs-12 text-muted mb-0">Create your first top hero slider or middle promo banner for the home index page using the form on the left.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- EDIT HOME BANNER MODAL -->
<div class="modal fade" id="editBannerModal" tabindex="-1" aria-labelledby="editBannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-3">
                <h5 class="modal-title fw-bold text-dark" id="editBannerModalLabel">
                    <iconify-icon icon="solar:pen-bold" class="text-primary me-2"></iconify-icon> Edit Home Banner
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editBannerForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold text-dark fs-13">Banner Heading / Title *</label>
                            <input type="text" name="title" id="editTitle" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark fs-13">Offer Tag / Badge</label>
                            <input type="text" name="badge_text" id="editBadgeText" class="form-control">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark fs-13">Subheading / Description</label>
                            <input type="text" name="subtitle" id="editSubtitle" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark fs-13">Home Page Position *</label>
                            <select name="banner_type" id="editBannerType" class="form-select" required>
                                <option value="hero">Top Hero Slider (Carousel)</option>
                                <option value="promo">Middle Promo Banner (Offer Strip)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark fs-13">Slide Order</label>
                            <input type="number" name="sort_order" id="editSortOrder" class="form-control" min="1">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark fs-13">Click Action (When User Taps) *</label>
                            <select name="target_type" id="editTargetType" class="form-select" required onchange="toggleTargetFields('edit')">
                                <option value="category">Open Product Category</option>
                                <option value="product">Open Specific Product</option>
                                <option value="booking">Open 20% Booking Flow</option>
                                <option value="url">External / Custom URL</option>
                                <option value="none">No Action (Informational)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark fs-13">Button Label (CTA)</label>
                            <input type="text" name="button_text" id="editButtonText" class="form-control">
                        </div>

                        <!-- Target Selection -->
                        <div class="col-12" id="editCategoryField">
                            <label class="form-label fw-semibold text-dark fs-13">Select Category</label>
                            <select name="target_id" id="editCategoryId" class="form-select">
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }} (Code: {{ $cat->referral_category_code ?: $cat->slug }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 d-none" id="editProductField">
                            <label class="form-label fw-semibold text-dark fs-13">Select Product</label>
                            <select id="editProductId" class="form-select" onchange="syncProductId('edit')">
                                <option value="">-- Select Product --</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->name }} (₹{{ number_format($prod->mrp, 2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12" id="editUrlField">
                            <label class="form-label fw-semibold text-dark fs-13">Custom Link / Deep-link</label>
                            <input type="text" name="link_url" id="editLinkUrl" class="form-control">
                        </div>

                        <!-- Image File Replace -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark fs-13">Banner Image (Leave empty to keep current)</label>
                            <input type="file" name="image" class="form-control" accept="image/*" onchange="previewBannerImage(this, 'editImagePreviewContainer')">
                            <div class="mt-2 p-2 border rounded bg-light d-flex align-items-center gap-3">
                                <div>
                                    <span class="fs-11 text-muted d-block mb-1">Current Image:</span>
                                    <img id="editImagePreview" src="" alt="Banner Preview" style="height: 60px; object-fit: cover;" class="rounded border">
                                </div>
                            </div>
                        </div>

                        <!-- Active Switch -->
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editIsActive">
                                <label class="form-check-label fw-semibold text-dark fs-13" for="editIsActive">Display on Home Index Page (Active)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold px-3">
                        <iconify-icon icon="solar:check-circle-bold" class="me-1"></iconify-icon> Update Home Banner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Interactive Scripts -->
<script>
    function previewBannerImage(input, containerId) {
        const container = document.getElementById(containerId);
        const img = container.querySelector('img');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                container.classList.remove('d-none');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function toggleTargetFields(prefix) {
        const targetType = document.getElementById(prefix + 'TargetType').value;
        const catField   = document.getElementById(prefix + 'CategoryField');
        const prodField  = document.getElementById(prefix + 'ProductField');
        const urlField   = document.getElementById(prefix + 'UrlField');

        if (targetType === 'category') {
            catField.classList.remove('d-none');
            prodField.classList.add('d-none');
        } else if (targetType === 'product') {
            catField.classList.add('d-none');
            prodField.classList.remove('d-none');
        } else {
            catField.classList.add('d-none');
            prodField.classList.add('d-none');
        }
    }

    function syncProductId(prefix) {
        const prodSelect = document.getElementById(prefix + 'ProductId');
        const targetIdInput = document.getElementById(prefix + 'CategoryId');
        targetIdInput.value = prodSelect.value;
    }

    function openEditModal(banner) {
        const modalEl = document.getElementById('editBannerModal');
        const form = document.getElementById('editBannerForm');

        form.action = '/admin/banners/' + banner.id;
        document.getElementById('editTitle').value      = banner.title || '';
        document.getElementById('editSubtitle').value   = banner.subtitle || '';
        document.getElementById('editBadgeText').value  = banner.badge_text || '';
        document.getElementById('editBannerType').value = banner.banner_type || 'hero';
        document.getElementById('editSortOrder').value  = banner.sort_order || 1;
        document.getElementById('editTargetType').value = banner.target_type || 'category';
        document.getElementById('editButtonText').value = banner.button_text || 'Shop Now';
        document.getElementById('editLinkUrl').value    = banner.link_url || '';
        document.getElementById('editIsActive').checked = Boolean(banner.is_active);

        const imgPreview = document.getElementById('editImagePreview');
        imgPreview.src = banner.image_url || '';

        // Target selection prefill
        toggleTargetFields('edit');
        if (banner.target_type === 'category') {
            document.getElementById('editCategoryId').value = banner.target_id || '';
        } else if (banner.target_type === 'product') {
            document.getElementById('editProductId').value = banner.target_id || '';
            document.getElementById('editCategoryId').value = banner.target_id || '';
        }

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
</script>
@endsection
