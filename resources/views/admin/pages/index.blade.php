@extends('adminlayouts.vertical', ['title' => 'Pages & Policies'])

@section('title', 'Pages & Policy Management')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Pages & Policies</h4>
            <p class="text-muted small mb-0">Manage Privacy Policy, Terms & Conditions, and dynamic CMS pages with structured points</p>
        </div>
        <div>
            <a href="{{ route('admin.pages.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm">
                <iconify-icon icon="solar:add-circle-outline" class="fs-18"></iconify-icon>
                <span>Add New Page</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border border-light-subtle shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.pages.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0">
                            <iconify-icon icon="solar:magnifer-outline"></iconify-icon>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search by title, slug, or content..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-50">Filter</button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.pages.index') }}" class="btn btn-light border w-50">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border border-light-subtle shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0">Configured System Pages & Documents</h6>
            <span class="badge bg-light text-dark border">{{ $pages->total() }} Total Pages</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 60px;">#</th>
                            <th>Page Title & Slug</th>
                            <th>Key Points</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pages as $page)
                            <tr>
                                <td class="ps-3 text-muted fw-bold">{{ $page->sort_order ?? $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold text-dark fs-14">{{ $page->title }}</div>
                                    <div class="text-muted small">
                                        Slug: <span class="fw-medium text-secondary">{{ $page->slug }}</span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $pointsCount = is_array($page->points) ? count($page->points) : 0;
                                    @endphp
                                    <span class="badge {{ $pointsCount > 0 ? 'bg-info-subtle text-info border border-info-subtle' : 'bg-light text-muted border' }} rounded-pill px-2 py-1">
                                        <iconify-icon icon="solar:checklist-minimalistic-outline" class="me-1"></iconify-icon>
                                        {{ $pointsCount }} {{ Str::plural('Point', $pointsCount) }}
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('admin.pages.status', $page->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent" title="Click to toggle status">
                                            @if($page->is_active)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                                    ● Active
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill">
                                                    ○ Inactive
                                                </span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $page->updated_at ? $page->updated_at->format('d M Y, h:i A') : '-' }}</span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Edit Page -->
                                        <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-sm btn-outline-primary" title="Edit Page & Points">
                                            <iconify-icon icon="solar:pen-outline"></iconify-icon>
                                            <span class="d-none d-md-inline ms-1">Edit</span>
                                        </a>

                                        <!-- Delete Page -->
                                        <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete \'{{ $page->title }}\'?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Page">
                                                <iconify-icon icon="solar:trash-bin-trash-outline"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:document-outline" class="fs-36 d-block mb-2 text-muted"></iconify-icon>
                                    <span class="fw-semibold">No pages or policies found.</span>
                                    <div class="mt-2">
                                        <a href="{{ route('admin.pages.create') }}" class="btn btn-sm btn-primary">Add your first page</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($pages->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-end">
                {{ $pages->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
