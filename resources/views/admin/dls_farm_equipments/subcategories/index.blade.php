@extends('adminlayouts.vertical', ['title' => 'DLS Farm Equipment Subcategories'])

@section('title', 'DLS Farm Equipment Subcategories')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">DLS Farm Equipments – Subcategories</h4>
            <p class="text-muted small mb-0">Organize and group agricultural machines, tillers, pumps, and solar tools under subcategories</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.dls_farm_equipments.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-folder me-1"></i> Manage Farm Categories
            </a>
            <a href="{{ route('admin.dls_farm_equipments.products.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bx bx-box me-1"></i> View Farm Products
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
            <i class="bx bx-check-circle me-1 align-middle fs-5"></i>
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
            <strong>Error:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Add Subcategory Form Column -->
        <div class="col-lg-4">
            <div class="card border border-light-subtle shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bx bx-plus-circle text-primary me-1 align-middle"></i> Add Farm Subcategory
                    </h6>
                </div>
                <div class="card-body p-3">
                    <form action="{{ route('admin.dls_farm_equipments.subcategories.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Parent Category -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Parent Farm Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="" disabled {{ empty($categoryId) ? 'selected' : '' }}>-- Select Farm Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subcategory Name -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Subcategory Name *</label>
                            <input type="text" name="name" class="form-control" required
                                   placeholder="e.g. 5HP Submersible Pumps, Rotary Tillers, Solar Sprayers">
                        </div>

                        <!-- Image -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Subcategory Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Description</label>
                            <textarea name="description" class="form-control" rows="2"
                                      placeholder="Brief summary or highlight of this farm subcategory"></textarea>
                        </div>

                        <!-- Sort Order -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Display Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                            <i class="bx bx-save me-1"></i> Save Farm Subcategory
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Subcategories List Table Column -->
        <div class="col-lg-8">
            <div class="card border border-light-subtle shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="fw-bold text-dark mb-0">Farm Equipment Subcategories</h6>
                        <span class="badge bg-primary-subtle text-primary">{{ $subcategories->total() }} total</span>
                    </div>

                    <!-- Filter / Search Form -->
                    <form action="{{ route('admin.dls_farm_equipments.subcategories.index') }}" method="GET" class="d-flex align-items-center gap-2">
                        <select name="category_id" class="form-select form-select-sm" style="min-width: 170px;" onchange="this.form.submit()">
                            <option value="">All Farm Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>

                        <div class="input-group input-group-sm" style="max-width: 200px;">
                            <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Search subcategory...">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="bx bx-search"></i>
                            </button>
                        </div>

                        @if(!empty($categoryId) || !empty($search))
                            <a href="{{ route('admin.dls_farm_equipments.subcategories.index') }}" class="btn btn-sm btn-light text-muted" title="Clear filter">
                                <i class="bx bx-x"></i>
                            </a>
                        @endif
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 45px;">#</th>
                                    <th>Image</th>
                                    <th>Subcategory</th>
                                    <th>Parent Category</th>
                                    <th>Products</th>
                                    <th>Order</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subcategories as $sub)
                                    <tr>
                                        <td class="text-muted small">{{ $loop->iteration + ($subcategories->currentPage() - 1) * $subcategories->perPage() }}</td>
                                        <td>
                                            <img src="{{ \App\Helpers\ImageHelper::resolve($sub->image) }}"
                                                 alt="{{ $sub->name }}"
                                                 class="rounded border" width="40" height="40"
                                                 style="object-fit: cover;"
                                                 onerror="this.onerror=null;this.src='{{ asset('images/no-image.png') }}';">
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark fs-14">{{ $sub->name }}</div>
                                            <span class="text-muted font-monospace micro">{{ $sub->slug }}</span>
                                            @if($sub->description)
                                                <div class="text-muted micro text-truncate" style="max-width: 220px;" title="{{ $sub->description }}">
                                                    {{ $sub->description }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                <i class="bx bx-category me-1"></i>{{ $sub->category->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.dls_farm_equipments.products.index', ['category_id' => $sub->category_id]) }}" class="badge bg-secondary-subtle text-secondary fs-12 text-decoration-none">
                                                {{ $sub->products_count }} products
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-muted border">{{ $sub->sort_order }}</span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal" data-bs-target="#editFarmSubcategoryModal"
                                                        data-id="{{ $sub->id }}"
                                                        data-name="{{ $sub->name }}"
                                                        data-category-id="{{ $sub->category_id }}"
                                                        data-description="{{ $sub->description }}"
                                                        data-sort-order="{{ $sub->sort_order }}"
                                                        data-image="{{ \App\Helpers\ImageHelper::resolve($sub->image) }}"
                                                        data-action="{{ route('admin.dls_farm_equipments.subcategories.update', $sub->id) }}">
                                                    <i class="bx bx-edit"></i> Edit
                                                </button>
                                                <form action="{{ route('admin.dls_farm_equipments.subcategories.destroy', $sub->id) }}"
                                                      method="POST" onsubmit="return confirm('Are you sure you want to delete this farm equipment subcategory?');"
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bx bx-layers fs-32 d-block mb-2 text-muted"></i>
                                            <h6>No Farm Equipment Subcategories found</h6>
                                            <p class="small text-muted mb-0">Use the form on the left to add your first subcategory under farm equipment categories.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($subcategories->hasPages())
                        <div class="card-footer bg-white border-top py-3 d-flex justify-content-end">
                            {{ $subcategories->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editFarmSubcategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-white border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark">
                    <i class="bx bx-edit text-primary me-1"></i> Edit Farm Equipment Subcategory
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editFarmSubcategoryForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Parent Farm Category *</label>
                        <select name="category_id" id="editCategoryId" class="form-select" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Subcategory Name *</label>
                        <input type="text" name="name" id="editSubcategoryName" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Subcategory Image</label>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <img id="editCurrentImagePreview" src="" alt="Current Image" class="rounded border"
                                 width="44" height="44" style="object-fit: cover; display: none;">
                            <span id="editNoImageText" class="text-muted small">No image uploaded</span>
                        </div>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">Leave empty to retain existing image.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Description</label>
                        <textarea name="description" id="editSubcategoryDescription" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Display Order</label>
                        <input type="number" name="sort_order" id="editSortOrder" class="form-control" min="0">
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold px-3">Update Subcategory</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editFarmSubcategoryModal');
    if (!editModal) return;

    editModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const action = button.getAttribute('data-action');
        const name = button.getAttribute('data-name');
        const categoryId = button.getAttribute('data-category-id');
        const description = button.getAttribute('data-description') || '';
        const sortOrder = button.getAttribute('data-sort-order') || '0';
        const image = button.getAttribute('data-image');

        const form = document.getElementById('editFarmSubcategoryForm');
        form.action = action;

        document.getElementById('editSubcategoryName').value = name;
        document.getElementById('editCategoryId').value = categoryId;
        document.getElementById('editSubcategoryDescription').value = description;
        document.getElementById('editSortOrder').value = sortOrder;

        const imgPreview = document.getElementById('editCurrentImagePreview');
        const noImgText = document.getElementById('editNoImageText');

        if (image && !image.includes('no-image.png')) {
            imgPreview.src = image;
            imgPreview.style.display = 'block';
            noImgText.style.display = 'none';
        } else {
            imgPreview.style.display = 'none';
            noImgText.style.display = 'inline';
        }
    });
});
</script>
@endsection
