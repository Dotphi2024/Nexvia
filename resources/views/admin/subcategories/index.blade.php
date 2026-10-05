@extends('adminlayouts.vertical', ['title' => 'Subcategories'])

@section('title', 'Subcategory Management')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Subcategories</h4>
            <p class="text-muted small mb-0">Organize and group products under specific parent categories</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-folder me-1"></i> Manage Categories
            </a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bx bx-box me-1"></i> View Products
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
                        <i class="bx bx-plus-circle text-primary me-1 align-middle"></i> Add New Subcategory
                    </h6>
                </div>
                <div class="card-body p-3">
                    <form action="{{ route('admin.subcategories.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Parent Category -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Parent Category *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="" disabled {{ empty($categoryId) ? 'selected' : '' }}>-- Select Category --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }} ({{ ucfirst(str_replace('_', ' ', $cat->type)) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Subcategory Name -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Subcategory Name *</label>
                            <input type="text" name="name" class="form-control" required
                                   placeholder="e.g. High-Speed Scooters, Power Tillers, Smart LED 4K">
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
                                      placeholder="Brief summary or highlight of this subcategory"></textarea>
                        </div>

                        <!-- Sort Order -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Display Order</label>
                            <input type="number" name="sort_order" class="form-control" value="0" min="0">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
                            <i class="bx bx-save me-1"></i> Save Subcategory
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Subcategories List Table Column -->
        <div class="col-lg-8">
            <div class="card border border-light-subtle shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <h6 class="fw-bold text-dark mb-0">Active Subcategories</h6>
                        </div>
                        <div class="col-md-7">
                            <form action="{{ route('admin.subcategories.index') }}" method="GET" class="d-flex gap-2">
                                <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="text" name="search" class="form-control form-control-sm"
                                       placeholder="Search..." value="{{ $search }}">
                                <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bx bx-search"></i></button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Image</th>
                                    <th>Subcategory Name</th>
                                    <th>Parent Category</th>
                                    <th>Products</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subcategories as $sub)
                                    <tr>
                                        <td class="text-muted small">{{ $subcategories->firstItem() + $loop->index }}</td>
                                        <td>
                                            <img src="{{ \App\Helpers\ImageHelper::resolve($sub->image) }}"
                                                 alt="{{ $sub->name }}" class="rounded border" width="40" height="40"
                                                 style="object-fit: cover;"
                                                 onerror="this.onerror=null;this.src='{{ asset('images/no-image.png') }}';">
                                        </td>
                                        <td>
                                            <div>
                                                <strong class="text-dark fs-14">{{ $sub->name }}</strong>
                                            </div>
                                            <span class="text-muted micro font-monospace">{{ $sub->slug }}</span>
                                        </td>
                                        <td>
                                            @if($sub->category)
                                                <span class="badge bg-light text-dark border px-2 py-1">
                                                    <i class="bx bx-folder me-1 text-primary"></i>{{ $sub->category->name }}
                                                </span>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.products.index', ['category_id' => $sub->category_id]) }}" class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1 text-decoration-none">
                                                <i class="bx bx-box me-1"></i>{{ $sub->products_count }} Products
                                            </a>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal" data-bs-target="#editSubcategoryModal"
                                                        data-id="{{ $sub->id }}"
                                                        data-category_id="{{ $sub->category_id }}"
                                                        data-name="{{ $sub->name }}"
                                                        data-description="{{ $sub->description }}"
                                                        data-sort_order="{{ $sub->sort_order }}"
                                                        data-image="{{ \App\Helpers\ImageHelper::resolve($sub->image) }}"
                                                        data-action="{{ route('admin.subcategories.update', $sub->id) }}">
                                                    <i class="bx bx-edit"></i>
                                                </button>

                                                <form action="{{ route('admin.subcategories.destroy', $sub->id) }}" method="POST"
                                                      onsubmit="return confirm('Are you sure you want to delete this subcategory?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bx bx-info-circle fs-3 d-block mb-1"></i>
                                            No subcategories found. Add your first subcategory using the form on the left!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($subcategories->hasPages())
                    <div class="card-footer bg-white border-top py-2">
                        {{ $subcategories->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Edit Subcategory Modal -->
<div class="modal fade" id="editSubcategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editSubcategoryForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Subcategory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Parent Category *</label>
                        <select name="category_id" id="editCategorySelect" class="form-select" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Subcategory Name *</label>
                        <input type="text" name="name" id="editSubcategoryName" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Change Image</label>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <img id="editImagePreview" src="" alt="preview" class="rounded border" width="45" height="45" style="object-fit:cover;">
                            <span class="text-muted micro">Current image preview</span>
                        </div>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Description</label>
                        <textarea name="description" id="editSubcategoryDesc" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Display Order</label>
                        <input type="number" name="sort_order" id="editSortOrder" class="form-control" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold">Update Subcategory</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var editModal = document.getElementById('editSubcategoryModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var action = button.getAttribute('data-action');
            var catId = button.getAttribute('data-category_id');
            var name = button.getAttribute('data-name');
            var desc = button.getAttribute('data-description');
            var sort = button.getAttribute('data-sort_order');
            var image = button.getAttribute('data-image');

            var form = document.getElementById('editSubcategoryForm');
            form.action = action;

            document.getElementById('editCategorySelect').value = catId;
            document.getElementById('editSubcategoryName').value = name;
            document.getElementById('editSubcategoryDesc').value = desc || '';
            document.getElementById('editSortOrder').value = sort || '0';
            
            var preview = document.getElementById('editImagePreview');
            if (image) {
                preview.src = image;
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        });
    }
});
</script>
@endsection
