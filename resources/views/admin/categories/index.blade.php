@extends('adminlayouts.vertical', ['title' => 'Categories'])

@section('title', 'Category & Product Credit Management')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Categories & Product Credit Slabs</h4>
            <p class="text-muted small mb-0">Manage product categories, category images, and referral Product Credit percentage slabs (10% to 20%)</p>
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
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Starting Product Credit (%) *</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="commission_percentage" class="form-control" value="10.00" required>
                                <span class="input-group-text bg-light fw-bold">% Credit</span>
                            </div>
                            <span class="text-muted micro d-block mt-1">Tier progression: 1st=10%, 2nd=12%, 3rd=15%, 4th=18%, 5th+=20%</span>
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
                                    <th>Product Credit Slabs</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                    <tr>
                                        <td class="text-muted small">{{ $loop->iteration }}</td>
                                        <td>
                                            @if($category->image)
                                                <img src="{{ asset($category->image) }}" alt="category" class="rounded border" width="40" height="40" style="object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted" style="width: 40px; height: 40px;">
                                                    <iconify-icon icon="solar:box-bold" class="fs-18"></iconify-icon>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-dark d-block fs-14">{{ $category->name }}</strong>
                                            <span class="text-muted micro">{{ $category->slug }}</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $category->type === 'electric_mobility' ? 'bg-warning text-dark' : 'bg-primary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $category->type)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success fw-bold">
                                                10% → 20% Product Credit
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Delete category?');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                            </form>
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
@endsection
