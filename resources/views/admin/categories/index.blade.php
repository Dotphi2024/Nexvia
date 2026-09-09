@extends('adminlayouts.vertical', ['title' => 'Categories'])

@section('title', 'Category & Product Credit Management')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Categories</h4>
            <p class="text-muted small mb-0">Manage product categories and category images</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Add Category Form -->
        <div class="col-lg-4">
            <div class="card border border-light-subtle shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">Add New Category</h6>
                </div>
                <div class="card-body p-3">
                    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Category Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Electric Scooters">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Category Type *</label>
                            <select name="type" class="form-select" required>
                                <option value="electronics">Electronics</option>
                                <option value="electric_mobility">Electric Mobility</option>
                                <option value="appliances">Appliances</option>
                                <option value="vehicles">Vehicles</option>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-7">
                                <label class="form-label fw-semibold text-dark">Referral Code (e.g. TV, AC, EV)</label>
                                <input type="text" name="referral_category_code" class="form-control text-uppercase" placeholder="TV" maxlength="10">
                            </div>
                            <div class="col-md-5 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="referral_eligible" value="1" class="form-check-input" id="refEligible" checked>
                                    <label class="form-check-label fw-semibold text-dark small" for="refEligible">Referral Eligible</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Starting Product Credit (%)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="commission_percentage" class="form-control bg-light" value="10.00" readonly>
                                <span class="input-group-text bg-light fw-bold text-muted">% Credit</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Category Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Brief summary of category"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-semibold">Save Category</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Categories List Table -->
        <div class="col-lg-8">
            <div class="card border border-light-subtle shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-dark mb-0">Active Product Categories</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Category Name</th>
                                    <th>Type</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                    <tr>
                                        <td class="text-muted small">{{ $loop->iteration }}</td>
                                        <td>
                                            <img src="{{ \App\Helpers\ImageHelper::resolve($category->image) }}" 
                                                 alt="category" 
                                                 class="rounded border" 
                                                 width="40" 
                                                 height="40" 
                                                 style="object-fit: cover;"
                                                 onerror="this.onerror=null;this.src='{{ asset('images/no-image.png') }}';">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-1">
                                                <strong class="text-dark fs-14">{{ $category->name }}</strong>
                                                @if($category->referral_category_code)
                                                    <span class="badge bg-dark-subtle text-dark font-monospace">{{ $category->referral_category_code }}</span>
                                                @endif
                                            </div>
                                            <span class="text-muted micro">{{ $category->slug }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $category->type === 'electric_mobility' ? 'bg-warning text-dark' : 'bg-primary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $category->type)) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editCategoryModal"
                                                        data-id="{{ $category->id }}"
                                                        data-name="{{ $category->name }}"
                                                        data-type="{{ $category->type }}"
                                                        data-referral-code="{{ $category->referral_category_code }}"
                                                        data-referral-eligible="{{ $category->referral_eligible ? '1' : '0' }}"
                                                        data-commission="{{ $category->commission_percentage }}"
                                                        data-description="{{ $category->description }}"
                                                        data-image="{{ \App\Helpers\ImageHelper::resolve($category->image) }}"
                                                        data-action="{{ route('admin.categories.update', $category->id) }}">
                                                    <i class="bx bx-edit"></i> Edit
                                                </button>
                                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Delete category?');" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-white border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark" id="editCategoryModalLabel">
                    <i class="bx bx-edit text-primary me-1"></i> Edit Category
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCategoryForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Category Name *</label>
                        <input type="text" name="name" id="editCategoryName" class="form-control" required placeholder="Category Name">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Category Type *</label>
                        <select name="type" id="editCategoryType" class="form-select" required>
                            <option value="electronics">Electronics</option>
                            <option value="electric_mobility">Electric Mobility</option>
                            <option value="appliances">Appliances</option>
                            <option value="vehicles">Vehicles</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold text-dark">Referral Code (e.g. TV, AC, EV)</label>
                            <input type="text" name="referral_category_code" id="editCategoryRefCode" class="form-control text-uppercase" maxlength="10">
                        </div>
                        <div class="col-md-5 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input type="checkbox" name="referral_eligible" value="1" class="form-check-input" id="editRefEligible">
                                <label class="form-check-label fw-semibold text-dark small" for="editRefEligible">Referral Eligible</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Category Image</label>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <img id="editCurrentImagePreview" src="" alt="Current Image" class="rounded border" width="48" height="48" style="object-fit: cover; display: none;">
                            <span id="editNoImageText" class="text-muted small">No image uploaded</span>
                        </div>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">Leave empty to keep the current image.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Description</label>
                        <textarea name="description" id="editCategoryDescription" class="form-control" rows="2" placeholder="Brief summary of category"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                        <i class="bx bx-check me-1"></i> Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editCategoryModal');
    if (!editModal) return;

    editModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        if (!button) return;

        const action = button.getAttribute('data-action');
        const name = button.getAttribute('data-name') || '';
        const type = button.getAttribute('data-type') || 'electronics';
        const refCode = button.getAttribute('data-referral-code') || '';
        const refEligible = button.getAttribute('data-referral-eligible') === '1';
        const description = button.getAttribute('data-description') || '';
        const image = button.getAttribute('data-image') || '';

        const form = document.getElementById('editCategoryForm');
        form.action = action;

        document.getElementById('editCategoryName').value = name;
        document.getElementById('editCategoryType').value = type;
        document.getElementById('editCategoryRefCode').value = refCode;
        document.getElementById('editRefEligible').checked = refEligible;
        document.getElementById('editCategoryDescription').value = description;

        const preview = document.getElementById('editCurrentImagePreview');
        const noImageText = document.getElementById('editNoImageText');
        if (image && !image.includes('no-image.png')) {
            preview.src = image;
            preview.style.display = 'inline-block';
            noImageText.style.display = 'none';
        } else {
            preview.style.display = 'none';
            noImageText.style.display = 'inline-block';
        }
    });
});
</script>
@endsection
