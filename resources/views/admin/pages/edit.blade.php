@extends('adminlayouts.vertical', ['title' => 'Edit Page - ' . $page->title])

@section('title', 'Edit Page & Points: ' . $page->title)

@section('content')
    <div class="container-fluid py-3">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">Edit Page: {{ $page->title }}</h4>
                <p class="text-muted small mb-0">Update page content, SEO tags, and structured policy points</p>
            </div>
            <div>
                <a href="{{ route('admin.pages.index') }}"
                    class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                    <iconify-icon icon="solar:arrow-left-outline"></iconify-icon>
                    <span>Back to List</span>
                </a>
            </div>
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

        <form action="{{ route('admin.pages.update', $page->id) }}" method="POST" id="pageForm">
            @csrf
            @method('PUT')

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
                                <label class="form-label fw-semibold text-dark">Page Title <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="title" id="pageTitle"
                                    class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title', $page->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">URL Slug</label>
                                <input type="text" name="slug" id="pageSlug"
                                    class="form-control @error('slug') is-invalid @enderror"
                                    value="{{ old('slug', $page->slug) }}" placeholder="e.g. privacy-policy">
                                <small class="text-muted">Unique page URL identifier.</small>
                                @error('slug')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">Excerpt / Short Overview</label>
                                <textarea name="excerpt" class="form-control"
                                    rows="2">{{ old('excerpt', $page->excerpt) }}</textarea>
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-semibold text-dark">Full Page Content (HTML / Text)</label>
                                <textarea name="content" class="form-control font-monospace"
                                    rows="8">{{ old('content', $page->content) }}</textarea>
                                <small class="text-muted">Write plain text or HTML (headings, paragraphs, lists).</small>
                            </div>
                        </div>
                    </div>

                    <!-- Structured Points Card -->
                    <div class="card border border-light-subtle shadow-sm mb-4">
                        <div
                            class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Structured Key Points / Sections</h6>
                                <small class="text-muted">Manage numbered points and section highlights</small>
                            </div>
                            <button type="button"
                                class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                id="addPointBtn">
                                <iconify-icon icon="solar:add-circle-outline"></iconify-icon>
                                <span>Add Point</span>
                            </button>
                        </div>
                        <div class="card-body p-4">
                            <div id="pointsContainer" class="d-flex flex-column gap-3">
                                @php
                                    $existingPoints = old('points', $page->points ?? []);
                                @endphp

                                @if(is_array($existingPoints) && count($existingPoints) > 0)
                                    @foreach($existingPoints as $index => $point)
                                        <div class="point-row p-3 rounded border border-light-subtle bg-light-subtle position-relative"
                                            data-index="{{ $index }}">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-primary text-white point-badge">Point
                                                    #{{ $loop->iteration }}</span>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-danger py-0 px-2 remove-point-btn"
                                                    title="Remove this point">
                                                    <iconify-icon icon="solar:trash-bin-minimalistic-outline"></iconify-icon> Remove
                                                </button>
                                            </div>
                                            <div class="mb-2">
                                                <input type="text" name="points[{{ $index }}][title]"
                                                    class="form-control form-control-sm fw-semibold"
                                                    value="{{ is_array($point) ? ($point['title'] ?? '') : '' }}"
                                                    placeholder="Point Title (e.g. Information We Collect)">
                                            </div>
                                            <div>
                                                <textarea name="points[{{ $index }}][description]"
                                                    class="form-control form-control-sm" rows="2"
                                                    placeholder="Point Description or details...">{{ is_array($point) ? ($point['description'] ?? '') : (is_string($point) ? $point : '') }}</textarea>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="point-row p-3 rounded border border-light-subtle bg-light-subtle position-relative"
                                        data-index="0">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge bg-primary text-white point-badge">Point #1</span>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger py-0 px-2 remove-point-btn"
                                                title="Remove this point">
                                                <iconify-icon icon="solar:trash-bin-minimalistic-outline"></iconify-icon> Remove
                                            </button>
                                        </div>
                                        <div class="mb-2">
                                            <input type="text" name="points[0][title]"
                                                class="form-control form-control-sm fw-semibold"
                                                placeholder="Point Title (e.g. Information We Collect)">
                                        </div>
                                        <div>
                                            <textarea name="points[0][description]" class="form-control form-control-sm"
                                                rows="2" placeholder="Point Description or explanation..."></textarea>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="addPointBtnBottom">
                                    <iconify-icon icon="solar:add-circle-outline" class="me-1"></iconify-icon> Add Another
                                    Point
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
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActiveSwitch"
                                    value="1" {{ old('is_active', $page->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-dark" for="isActiveSwitch">Active /
                                    Published</label>
                                <small class="d-block text-muted">When active, this page is publicly visible.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark small">Display Sort Order</label>
                                <input type="number" name="sort_order" class="form-control form-control-sm"
                                    value="{{ old('sort_order', $page->sort_order) }}" min="0">
                                <small class="text-muted">Controls display order in lists.</small>
                            </div>

                            <div class="p-2 bg-light rounded border border-light-subtle mb-3 small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Created:</span>
                                    <span
                                        class="fw-semibold text-dark">{{ $page->created_at ? $page->created_at->format('d M Y') : '-' }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Last Updated:</span>
                                    <span
                                        class="fw-semibold text-dark">{{ $page->updated_at ? $page->updated_at->format('d M Y, h:i A') : '-' }}</span>
                                </div>
                            </div>

                            <hr class="my-3">

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm mb-2">
                                Update Page & Points
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
                                <input type="text" name="meta_title" class="form-control form-control-sm"
                                    value="{{ old('meta_title', $page->meta_title) }}" placeholder="SEO page title">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark small">Meta Description</label>
                                <textarea name="meta_description" class="form-control form-control-sm" rows="3"
                                    placeholder="Search engine snippet summary...">{{ old('meta_description', $page->meta_description) }}</textarea>
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-semibold text-dark small">Meta Keywords</label>
                                <input type="text" name="meta_keywords" class="form-control form-control-sm"
                                    value="{{ old('meta_keywords', $page->meta_keywords) }}"
                                    placeholder="policy, privacy, terms">
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
        $(document).ready(function () {
            let pointIndex = {{ is_array($existingPoints) ? count($existingPoints) : 1 }};

            function addPointRow() {
                let currentCount = $('.point-row').length + 1;
                let pointHtml = `
                <div class="point-row p-3 rounded border border-light-subtle bg-light-subtle position-relative" data-index="${pointIndex}">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary text-white point-badge">Point #${currentCount}</span>
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

            $('#addPointBtn, #addPointBtnBottom').on('click', function (e) {
                e.preventDefault();
                addPointRow();
            });

            $(document).on('click', '.remove-point-btn', function () {
                if ($('.point-row').length > 1) {
                    $(this).closest('.point-row').fadeOut(200, function () {
                        $(this).remove();
                        updatePointNumbers();
                    });
                } else {
                    let row = $(this).closest('.point-row');
                    row.find('input, textarea').val('');
                }
            });

            function updatePointNumbers() {
                $('.point-row').each(function (idx) {
                    $(this).find('.point-badge').text('Point #' + (idx + 1));
                });

            }
        });
    </script>
@endsection