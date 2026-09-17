@extends('adminlayouts.vertical', ['title' => 'Create Page'])

@section('title', 'Add New Page & Points')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Create Page / Policy</h4>
            <p class="text-muted small mb-0">Configure page details, full content, and key points</p>
        </div>
        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
            <iconify-icon icon="solar:arrow-left-outline"></iconify-icon>
            <span>Back to List</span>
        </a>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
            <h6 class="fw-bold mb-1">Validation Errors:</h6>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('admin.pages.store') }}" method="POST" id="pageForm">
        @csrf

        <div class="row g-4">
            <!-- Left Column: Main Details & Points Repeater -->
            <div class="col-lg-8">
                <!-- Main Info Card -->
                <div class="card border border-light-subtle shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="fw-bold text-dark mb-0">Page Information</h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Page Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="pageTitle" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="e.g. Privacy Policy, Terms of Service" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">URL Slug</label>
                            <input type="text" name="slug" id="pageSlug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="e.g. privacy-policy (leave blank to auto-generate)">
                            <small class="text-muted">Slug will be auto-generated from title if left empty.</small>
                            @error('slug')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Excerpt / Short Overview</label>
                            <textarea name="excerpt" class="form-control" rows="2" placeholder="Brief 1-2 sentence description summarizing this document...">{{ old('excerpt') }}</textarea>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold text-dark">Full Page Content (HTML / Text)</label>
                            <textarea name="content" class="form-control font-monospace" rows="8" placeholder="<p>Full document body, terms, or policy text...</p>">{{ old('content') }}</textarea>
                            <small class="text-muted">You can write plain text or HTML formatting (headings, paragraphs, lists).</small>
                        </div>
                    </div>
                </div>

                <!-- Structured Points Card -->
                <div class="card border border-light-subtle shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Structured Key Points / Sections</h6>
                            <small class="text-muted">Add key points or section highlights (e.g. Point 1: Information Collection, Point 2: Cookies)</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" id="addPointBtn">
                            <iconify-icon icon="solar:add-circle-outline"></iconify-icon>
                            <span>Add Point</span>
                        </button>
                    </div>
                    <div class="card-body p-4">
                        <div id="pointsContainer" class="d-flex flex-column gap-3">
                            <!-- Template / Default first point -->
                            <div class="point-row p-3 rounded border border-light-subtle bg-light-subtle position-relative" data-index="0">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary text-white point-badge">Point #1</span>
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 remove-point-btn" title="Remove this point">
                                        <iconify-icon icon="solar:trash-bin-minimalistic-outline"></iconify-icon> Remove
                                    </button>
                                </div>
                                <div class="mb-2">
                                    <input type="text" name="points[0][title]" class="form-control form-control-sm fw-semibold" placeholder="Point Title (e.g. Information We Collect)">
                                </div>
                                <div>
                                    <textarea name="points[0][description]" class="form-control form-control-sm" rows="2" placeholder="Point Description or explanation..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="addPointBtnBottom">
                                <iconify-icon icon="solar:add-circle-outline" class="me-1"></iconify-icon> Add Another Point
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Settings & SEO -->
            <div class="col-lg-4">
                <!-- Publishing Controls -->
                <div class="card border border-light-subtle shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="fw-bold text-dark mb-0">Publishing Settings</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActiveSwitch" value="1" checked>
                            <label class="form-check-label fw-semibold text-dark" for="isActiveSwitch">Active / Published</label>
                            <small class="d-block text-muted">When active, this page will be publicly visible.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Display Sort Order</label>
                            <input type="number" name="sort_order" class="form-control form-control-sm" value="{{ old('sort_order', 1) }}" min="0">
                            <small class="text-muted">Controls display order in lists.</small>
                        </div>

                        <hr class="my-3">

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-2">
                            Save & Publish Page
                        </button>
                        <a href="{{ route('admin.pages.index') }}" class="btn btn-light border w-100 py-2">
                            Cancel
                        </a>
                    </div>
                </div>

                <!-- SEO Meta Information -->
                <div class="card border border-light-subtle shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h6 class="fw-bold text-dark mb-0">SEO & Metadata</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Meta Title</label>
                            <input type="text" name="meta_title" class="form-control form-control-sm" value="{{ old('meta_title') }}" placeholder="SEO page title">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Meta Description</label>
                            <textarea name="meta_description" class="form-control form-control-sm" rows="3" placeholder="Search engine snippet summary...">{{ old('meta_description') }}</textarea>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold text-dark small">Meta Keywords</label>
                            <input type="text" name="meta_keywords" class="form-control form-control-sm" value="{{ old('meta_keywords') }}" placeholder="policy, privacy, terms">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let pointIndex = 1;

    // Auto-generate slug from title if slug not manually modified
    let manualSlug = false;
    $('#pageSlug').on('input', function() {
        manualSlug = $(this).val().length > 0;
    });

    $('#pageTitle').on('input', function() {
        if (!manualSlug) {
            let slug = $(this).val()
                .toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            $('#pageSlug').val(slug);
        }
    });

    // Add Point Function
    function addPointRow() {
        let pointHtml = `
            <div class="point-row p-3 rounded border border-light-subtle bg-light-subtle position-relative" data-index="${pointIndex}">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-primary text-white point-badge">Point #${pointIndex + 1}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 remove-point-btn" title="Remove this point">
                        <iconify-icon icon="solar:trash-bin-minimalistic-outline"></iconify-icon> Remove
                    </button>
                </div>
                <div class="mb-2">
                    <input type="text" name="points[${pointIndex}][title]" class="form-control form-control-sm fw-semibold" placeholder="Point Title (e.g. Data Security & Storage)">
                </div>
                <div>
                    <textarea name="points[${pointIndex}][description]" class="form-control form-control-sm" rows="2" placeholder="Point Description or explanation..."></textarea>
                </div>
            </div>
        `;
        $('#pointsContainer').append(pointHtml);
        pointIndex++;
        updatePointNumbers();
    }

    $('#addPointBtn, #addPointBtnBottom').on('click', function(e) {
        e.preventDefault();
        addPointRow();
    });

    // Remove Point Function
    $(document).on('click', '.remove-point-btn', function() {
        if ($('.point-row').length > 1) {
            $(this).closest('.point-row').fadeOut(200, function() {
                $(this).remove();
                updatePointNumbers();
            });
        } else {
            // If only 1 row left, just clear fields instead of removing
            let row = $(this).closest('.point-row');
            row.find('input, textarea').val('');
        }
    });

    function updatePointNumbers() {
        $('.point-row').each(function(idx) {
            $(this).find('.point-badge').text('Point #' + (idx + 1));
        });
    }
});
</script>
@endsection
