@extends('adminlayouts.vertical', ['title' => 'DLS Farm Equipments Banners'])

@section('title', 'DLS Agro Banners Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">DLS Farm Equipments Banners</h4>
            <p class="text-muted small mb-0">Manage sliders, middle promo strips, and side promotional banners displayed for <strong class="text-success">DLS Agro & Farm Equipment</strong> products.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dls_farm_equipments.products.index') }}" class="btn btn-outline-success btn-sm rounded-3">
                <iconify-icon icon="solar:box-minimalistic-outline" class="align-middle me-1"></iconify-icon> Agro Products
            </a>
            <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
                <iconify-icon icon="solar:gallery-wide-outline" class="align-middle me-1"></iconify-icon> Home Banners
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
            <iconify-icon icon="solar:check-circle-bold" class="fs-18 me-1 align-middle text-success"></iconify-icon>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
            <ul class="mb-0 small ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- KPI Metrics Row -->
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                <span class="text-muted fs-11 text-uppercase fw-semibold">Total Agro Banners</span>
                <h3 class="fw-bold text-dark mb-0 mt-1">{{ $totalBanners }}</h3>
                <span class="fs-11 text-success mt-1 d-block"><iconify-icon icon="solar:leaf-bold"></iconify-icon> Agro Section</span>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);">
                <span class="text-muted fs-11 text-uppercase fw-semibold">Active Banners</span>
                <h3 class="fw-bold text-success mb-0 mt-1">{{ $activeBanners }}</h3>
                <span class="fs-11 text-muted mt-1 d-block">Live on app/web</span>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3" style="background: linear-gradient(135deg, #fefce8 0%, #fef08a 100%);">
                <span class="text-muted fs-11 text-uppercase fw-semibold">Top Hero Sliders</span>
                <h3 class="fw-bold text-warning mb-0 mt-1">{{ $heroBanners }}</h3>
                <span class="fs-11 text-muted mt-1 d-block">Carousel sliders</span>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                <span class="text-muted fs-11 text-uppercase fw-semibold">Middle Promos</span>
                <h3 class="fw-bold text-primary mb-0 mt-1">{{ $promoBanners }}</h3>
                <span class="fs-11 text-muted mt-1 d-block">Offer strips</span>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3" style="background: linear-gradient(135deg, #fdf4ff 0%, #fae8ff 100%);">
                <span class="text-muted fs-11 text-uppercase fw-semibold">Side Banners</span>
                <h3 class="fw-bold text-purple mb-0 mt-1" style="color: #9333ea;">{{ $sideBanners }}</h3>
                <span class="fs-11 text-muted mt-1 d-block">Sidebars / Vertical</span>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-6">
            <div class="card border-0 shadow-sm rounded-3 h-100 p-3" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                <span class="text-muted fs-11 text-uppercase fw-semibold">Total Engagement</span>
                <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($totalClicks) }}</h3>
                <span class="fs-11 text-muted mt-1 d-block">Customer taps</span>
            </div>
        </div>
    </div>

    <!-- Main Two-Column Layout -->
    <div class="row g-4">
        <!-- Add Agro Banner Form (Left Column) -->
        <div class="col-xl-4 col-lg-5">
            <div class="card border border-light-subtle shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:add-circle-bold" class="text-success fs-18"></iconify-icon>
                        Add DLS Agro Banner
                    </h6>
                    <span class="badge bg-success-subtle text-success fw-semibold px-2 py-1 fs-11">Agro Section</span>
                </div>
                <div class="card-body p-3">
                    <form action="{{ route('admin.dls_farm_equipments.banners.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Banner Title -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark fs-13">Banner Heading / Title *</label>
                            <input type="text" name="title" class="form-control form-control-sm" required placeholder="e.g. Modern Farm Power Tillers & Harvesters" value="{{ old('title') }}">
                        </div>

                        <!-- Subtitle / Tagline -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark fs-13">Subheading / Description</label>
                            <input type="text" name="subtitle" class="form-control form-control-sm" placeholder="e.g. Book now with 20% deposit & 60 days flexible balance" value="{{ old('subtitle') }}">
                        </div>

                        <!-- Badge Text -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark fs-13">Offer Tag / Badge (Optional)</label>
                            <input type="text" name="badge_text" class="form-control form-control-sm" placeholder="e.g. 5% GST BENEFIT or KISAN SPECIAL" value="{{ old('badge_text') }}">
                        </div>

                        <!-- Position & Type -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-7">
                                <label class="form-label fw-semibold text-dark fs-13">Agro Display Position *</label>
                                <select name="banner_type" class="form-select form-select-sm" required>
                                    <option value="hero" selected>Top Hero Slider (Carousel)</option>
                                    <option value="promo">Middle Promo Banner (Offer Strip)</option>
                                    <option value="side">Side Banner (Sidebar Promo)</option>
                                    <option value="popup">Popup Offer Modal</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-semibold text-dark fs-13">Sort Order</label>
                                <input type="number" name="sort_order" class="form-control form-control-sm" min="1" value="{{ old('sort_order', 1) }}" placeholder="1">
                            </div>
                        </div>

                        <!-- Target Action Type -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark fs-13">Click Action (When User Taps) *</label>
                            <select name="target_type" id="createTargetType" class="form-select form-select-sm" required onchange="toggleTargetFields('create')">
                                <option value="category">Open Agro Category</option>
                                <option value="product">Open Specific Agro Product</option>
                                <option value="booking">Open 20% Booking Flow</option>
                                <option value="url">External / Custom URL</option>
                                <option value="none">No Action (Informational)</option>
                            </select>
                        </div>

                        <!-- Category Selector (DLS Agro Only) -->
                        <div class="mb-3" id="createCategoryField">
                            <label class="form-label fw-semibold text-dark fs-13">Select Agro Category</label>
                            <select name="target_id" id="createCategoryId" class="form-select form-select-sm">
                                <option value="">-- Select Agro Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }} (Code: {{ $cat->referral_category_code ?: $cat->slug }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Product Selector (DLS Agro Only) -->
                        <div class="mb-3 d-none" id="createProductField">
                            <label class="form-label fw-semibold text-dark fs-13">Select Agro Product</label>
                            <select id="createProductId" class="form-select form-select-sm" onchange="syncProductId('create')">
                                <option value="">-- Select Agro Product --</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->name }} (₹{{ number_format($prod->mrp, 2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Custom URL field -->
                        <div class="mb-3 d-none" id="createUrlField">
                            <label class="form-label fw-semibold text-dark fs-13">Custom Link / Deep-link</label>
                            <input type="text" name="link_url" id="createLinkUrl" class="form-control form-control-sm" placeholder="e.g. /dls-agro/category or https://..." value="{{ old('link_url') }}">
                        </div>

                        <!-- Button Call to Action -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark fs-13">Button Label (CTA)</label>
                            <input type="text" name="button_text" class="form-control form-control-sm" placeholder="e.g. Book Agro Equipment" value="{{ old('button_text', 'Book Now') }}">
                        </div>

                        <!-- Banner Image Upload -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark fs-13">Banner Image *</label>
                            <input type="file" name="image" id="createImageInput" class="form-control form-control-sm" accept="image/*" required onchange="previewBannerImage(this, 'createImagePreviewContainer')">
                            <div class="form-text fs-11 text-muted">Dimensions: 1200x500px for Hero, 1200x350px for Promo Strip, 400x600px for Side Banner (JPG, PNG, WEBP max 5MB).</div>
                            
                            <div id="createImagePreviewContainer" class="mt-2 text-center d-none p-2 border rounded bg-light">
                                <img id="createImagePreview" src="" alt="Banner Preview" class="img-fluid rounded" style="max-height: 120px; object-fit: cover;">
                            </div>
                        </div>

                        <!-- Active Toggle -->
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="createIsActive" checked>
                            <label class="form-check-label fw-semibold text-dark fs-13" for="createIsActive">Publish on Agro Section (Active)</label>
                        </div>

                        <button type="submit" class="btn btn-success w-100 fw-semibold py-2">
                            <iconify-icon icon="solar:upload-track-2-bold" class="me-1"></iconify-icon> Publish Agro Banner
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Banners List Table (Right Column) -->
        <div class="col-xl-8 col-lg-7">
            <div class="card border border-light-subtle shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Active DLS Agro Banners</h6>
                        <span class="fs-12 text-muted">Showing {{ $banners->count() }} banner(s)</span>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="d-flex flex-wrap gap-1">
                        <a href="{{ route('admin.dls_farm_equipments.banners.index') }}" class="btn btn-sm {{ !request('type') ? 'btn-dark' : 'btn-outline-secondary' }} fs-11 px-2 py-1">All Agro</a>
                        <a href="{{ route('admin.dls_farm_equipments.banners.index', ['type' => 'hero']) }}" class="btn btn-sm {{ request('type') == 'hero' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }} fs-11 px-2 py-1">Top Sliders</a>
                        <a href="{{ route('admin.dls_farm_equipments.banners.index', ['type' => 'promo']) }}" class="btn btn-sm {{ request('type') == 'promo' ? 'btn-primary text-white' : 'btn-outline-secondary' }} fs-11 px-2 py-1">Middle Strips</a>
                        <a href="{{ route('admin.dls_farm_equipments.banners.index', ['type' => 'side']) }}" class="btn btn-sm {{ request('type') == 'side' ? 'btn-success text-white' : 'btn-outline-secondary' }} fs-11 px-2 py-1">Side Banners</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light fs-12 text-uppercase text-muted">
                            <tr>
                                <th style="width: 120px;">Banner Image</th>
                                <th>Heading & Target</th>
                                <th>Position</th>
                                <th>Clicks</th>
                                <th>Order</th>
                                <th>Status</th>
                                <th class="text-end" style="width: 110px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($banners as $index => $banner)
                                <tr>
                                    <!-- Thumbnail -->
                                    <td>
                                        <div class="position-relative rounded overflow-hidden shadow-sm" style="width: 115px; height: 55px; background: #f8fafc;">
                                            <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="w-100 h-100" style="object-fit: cover;" onerror="this.src='https://placehold.co/115x55?text=Agro+Banner'">
                                            @if($banner->badge_text)
                                                <span class="position-absolute bottom-0 start-0 badge bg-danger fs-9 px-1 py-0 m-1 rounded-pill">
                                                    {{ $banner->badge_text }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Title & Target info -->
                                    <td>
                                        <h6 class="fw-bold text-dark mb-1 fs-13">{{ $banner->title }}</h6>
                                        @if($banner->subtitle)
                                            <p class="text-muted fs-11 mb-1 text-truncate" style="max-width: 250px;">{{ $banner->subtitle }}</p>
                                        @endif
                                        <div class="fs-11 text-muted">
                                            <span class="badge bg-light text-dark border">
                                                Target: {{ ucfirst($banner->target_type) }}
                                            </span>
                                            @if($banner->target_type == 'category' && $banner->category)
                                                <span class="text-primary fw-semibold ms-1">{{ $banner->category->name }}</span>
                                            @elseif($banner->target_type == 'product' && $banner->product)
                                                <span class="text-primary fw-semibold ms-1">{{ $banner->product->name }}</span>
                                            @elseif($banner->link_url)
                                                <span class="text-muted ms-1 text-truncate" style="max-width: 150px;">{{ $banner->link_url }}</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Position Badge -->
                                    <td>
                                        @if($banner->banner_type == 'hero')
                                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle fs-11">
                                                <iconify-icon icon="solar:slider-minimalistic-horizontal-bold" class="me-1"></iconify-icon> Top Slider
                                            </span>
                                        @elseif($banner->banner_type == 'promo')
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-11">
                                                <iconify-icon icon="solar:tag-bold" class="me-1"></iconify-icon> Middle Promo Strip
                                            </span>
                                        @elseif(in_array($banner->banner_type, ['side', 'side_banner']))
                                            <span class="badge bg-success-subtle text-success border border-success-subtle fs-11">
                                                <iconify-icon icon="solar:sidebar-minimalistic-bold" class="me-1"></iconify-icon> Side Banner
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary fs-11">
                                                {{ ucfirst($banner->banner_type) }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Clicks Metric -->
                                    <td>
                                        <span class="badge bg-light text-dark border fs-11">
                                            <iconify-icon icon="solar:cursor-bold" class="fs-10"></iconify-icon> {{ number_format($banner->clicks_count) }} taps
                                        </span>
                                    </td>

                                    <!-- Sort Order -->
                                    <td>
                                        <span class="badge bg-light text-secondary border fs-11 font-monospace">
                                            #{{ $banner->sort_order }}
                                        </span>
                                    </td>

                                    <!-- Active Toggle Switch -->
                                    <td>
                                        <form action="{{ route('admin.dls_farm_equipments.banners.status', $banner->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" role="switch" onchange="this.form.submit()" {{ $banner->is_active ? 'checked' : '' }} title="Click to toggle status">
                                            </div>
                                        </form>
                                        <small class="fs-10 {{ $banner->is_active ? 'text-success' : 'text-muted' }}">
                                            {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                        </small>
                                    </td>

                                    <!-- Actions -->
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-primary px-2 py-1" onclick="openAgroEditModal({{ json_encode($banner) }})" title="Edit Agro Banner">
                                                <iconify-icon icon="solar:pen-new-square-linear"></iconify-icon>
                                            </button>

                                            <form action="{{ route('admin.dls_farm_equipments.banners.destroy', $banner->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this Agro banner?');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" title="Delete Agro Banner">
                                                    <iconify-icon icon="solar:trash-bin-trash-linear"></iconify-icon>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <iconify-icon icon="solar:leaf-bold" class="fs-48 text-success-subtle mb-2"></iconify-icon>
                                            <h6 class="fw-bold text-dark">No DLS Agro Banners Found</h6>
                                            <p class="fs-12 text-muted mb-0">Create your first Agro hero slider, promo strip, or side banner using the form on the left.</p>
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

<!-- Edit Banner Modal -->
<div class="modal fade" id="editAgroBannerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:pen-bold" class="text-success"></iconify-icon>
                    Edit DLS Agro Banner
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAgroBannerForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark fs-13">Banner Heading / Title *</label>
                            <input type="text" name="title" id="editTitle" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark fs-13">Subheading / Description</label>
                            <input type="text" name="subtitle" id="editSubtitle" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark fs-13">Offer Tag / Badge</label>
                            <input type="text" name="badge_text" id="editBadgeText" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark fs-13">Agro Display Position *</label>
                            <select name="banner_type" id="editBannerType" class="form-select" required>
                                <option value="hero">Top Hero Slider (Carousel)</option>
                                <option value="promo">Middle Promo Banner (Offer Strip)</option>
                                <option value="side">Side Banner (Sidebar Promo)</option>
                                <option value="popup">Popup Offer Modal</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark fs-13">Sort Order</label>
                            <input type="number" name="sort_order" id="editSortOrder" class="form-control" min="1">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark fs-13">Click Action *</label>
                            <select name="target_type" id="editTargetType" class="form-select" required onchange="toggleTargetFields('edit')">
                                <option value="category">Open Agro Category</option>
                                <option value="product">Open Specific Agro Product</option>
                                <option value="booking">Open 20% Booking Flow</option>
                                <option value="url">External / Custom URL</option>
                                <option value="none">No Action (Informational)</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="editCategoryField">
                            <label class="form-label fw-semibold text-dark fs-13">Select Agro Category</label>
                            <select name="target_id" id="editCategoryId" class="form-select">
                                <option value="">-- Select Agro Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 d-none" id="editProductField">
                            <label class="form-label fw-semibold text-dark fs-13">Select Agro Product</label>
                            <select id="editProductId" class="form-select" onchange="syncProductId('edit')">
                                <option value="">-- Select Agro Product --</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->name }} (₹{{ number_format($prod->mrp, 2) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 d-none" id="editUrlField">
                            <label class="form-label fw-semibold text-dark fs-13">Custom Link / Deep-link</label>
                            <input type="text" name="link_url" id="editLinkUrl" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark fs-13">Button Label (CTA)</label>
                            <input type="text" name="button_text" id="editButtonText" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark fs-13">Replace Image (Optional)</label>
                            <input type="file" name="image" id="editImageInput" class="form-control" accept="image/*" onchange="previewBannerImage(this, 'editImagePreviewContainer')">
                        </div>

                        <div class="col-12" id="editImagePreviewContainer">
                            <label class="form-label fw-semibold text-dark fs-13 d-block">Current Banner Image</label>
                            <img id="editImagePreview" src="" alt="Current Banner" class="img-fluid rounded border" style="max-height: 120px; object-fit: cover;">
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editIsActive">
                                <label class="form-check-label fw-semibold text-dark fs-13" for="editIsActive">Publish on Agro Section (Active)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleTargetFields(mode) {
    const typeSelect = document.getElementById(mode + 'TargetType');
    const val = typeSelect.value;

    const catField = document.getElementById(mode + 'CategoryField');
    const prodField = document.getElementById(mode + 'ProductField');
    const urlField = document.getElementById(mode + 'UrlField');

    catField.classList.add('d-none');
    prodField.classList.add('d-none');
    urlField.classList.add('d-none');

    if (val === 'category') {
        catField.classList.remove('d-none');
    } else if (val === 'product') {
        prodField.classList.remove('d-none');
    } else if (val === 'url') {
        urlField.classList.remove('d-none');
    }
}

function syncProductId(mode) {
    const prodSelect = document.getElementById(mode + 'ProductId');
    let targetInput = document.getElementById(mode + 'CategoryId');
    if (targetInput) {
        targetInput.value = prodSelect.value;
    }
}

function previewBannerImage(input, containerId) {
    const container = document.getElementById(containerId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = container.querySelector('img');
            previewImg.src = e.target.result;
            container.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function openAgroEditModal(banner) {
    const form = document.getElementById('editAgroBannerForm');
    form.action = '/admin/dls-farm-equipments/banners/' + banner.id;

    document.getElementById('editTitle').value = banner.title || '';
    document.getElementById('editSubtitle').value = banner.subtitle || '';
    document.getElementById('editBadgeText').value = banner.badge_text || '';
    document.getElementById('editBannerType').value = banner.banner_type || 'hero';
    document.getElementById('editSortOrder').value = banner.sort_order || 1;
    document.getElementById('editTargetType').value = banner.target_type || 'category';
    document.getElementById('editLinkUrl').value = banner.link_url || '';
    document.getElementById('editButtonText').value = banner.button_text || 'Book Now';
    document.getElementById('editIsActive').checked = Boolean(banner.is_active);

    toggleTargetFields('edit');

    if (banner.target_type === 'category') {
        document.getElementById('editCategoryId').value = banner.target_id || '';
    } else if (banner.target_type === 'product') {
        document.getElementById('editProductId').value = banner.target_id || '';
        document.getElementById('editCategoryId').value = banner.target_id || '';
    }

    const editPreview = document.getElementById('editImagePreview');
    editPreview.src = banner.image_url || '';

    const modal = new bootstrap.Modal(document.getElementById('editAgroBannerModal'));
    modal.show();
}
</script>
@endsection
