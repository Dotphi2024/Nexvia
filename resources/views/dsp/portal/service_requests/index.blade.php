@extends('dsp.layouts.portal')

@section('title', 'Service & Problem Requests')

@section('content')
<div class="mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <span class="bg-primary text-white p-2 rounded-3 d-inline-flex">
                    <iconify-icon icon="solar:wrench-bold-duotone" class="fs-4"></iconify-icon>
                </span>
                Assigned Service & Problem Requests
            </h4>
            <p class="text-muted small mb-0">Attend customer complaints, warranty issues, and service requests in your allocated territory</p>
        </div>
        <div>
            <span class="badge bg-light text-secondary border px-3 py-2">
                <iconify-icon icon="solar:map-point-bold" class="text-danger me-1 align-middle"></iconify-icon>
                Territory: {{ Auth::guard('dsp')->user()->district ?? 'Local' }}, PIN: {{ Auth::guard('dsp')->user()->premises_pincode ?? 'N/A' }}
            </span>
        </div>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card-stat">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Total Assigned</span>
                <div class="stat-icon bg-primary-subtle text-primary">
                    <iconify-icon icon="solar:bill-list-bold"></iconify-icon>
                </div>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ number_format($stats['total'] ?? 0) }}</h3>
            <span class="text-muted micro">In your territory</span>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card-stat border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-warning-emphasis small fw-bold">Pending Attention</span>
                <div class="stat-icon bg-warning-subtle text-warning">
                    <iconify-icon icon="solar:clock-circle-bold"></iconify-icon>
                </div>
            </div>
            <h3 class="fw-bold text-warning-emphasis mb-0">{{ number_format($stats['pending_attention'] ?? 0) }}</h3>
            <span class="text-muted micro">Action required</span>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card-stat border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-success small fw-bold">Attended / In Progress</span>
                <div class="stat-icon bg-success-subtle text-success">
                    <iconify-icon icon="solar:check-circle-bold"></iconify-icon>
                </div>
            </div>
            <h3 class="fw-bold text-success mb-0">{{ number_format($stats['attended'] ?? 0) }}</h3>
            <span class="text-muted micro">Technician assigned</span>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card-stat">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-muted small fw-semibold">Resolved</span>
                <div class="stat-icon bg-secondary-subtle text-secondary">
                    <iconify-icon icon="solar:shield-check-bold"></iconify-icon>
                </div>
            </div>
            <h3 class="fw-bold text-secondary mb-0">{{ number_format($stats['resolved'] ?? 0) }}</h3>
            <span class="text-muted micro">Closed tickets</span>
        </div>
    </div>
</div>

<!-- Filter Tabs & Search -->
<div class="card-dsp p-3 mb-4">
    <div class="row g-3 align-items-center">
        <div class="col-lg-7">
            <ul class="nav nav-pills gap-1">
                <li class="nav-item">
                    <a class="nav-link {{ !request('attended') && !request('status') ? 'active' : '' }}" 
                       href="{{ route('dsp.service_requests.index') }}">
                        All ({{ $stats['total'] ?? 0 }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('attended') === 'no' ? 'active' : '' }}" 
                       href="{{ route('dsp.service_requests.index', ['attended' => 'no']) }}">
                        Pending Attention ({{ $stats['pending_attention'] ?? 0 }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('attended') === 'yes' && request('status') !== 'resolved' ? 'active' : '' }}" 
                       href="{{ route('dsp.service_requests.index', ['attended' => 'yes', 'status' => 'in_progress']) }}">
                        Attended / Active
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('status') === 'resolved' ? 'active' : '' }}" 
                       href="{{ route('dsp.service_requests.index', ['status' => 'resolved']) }}">
                        Resolved ({{ $stats['resolved'] ?? 0 }})
                    </a>
                </li>
            </ul>
        </div>

        <div class="col-lg-5">
            <form action="{{ route('dsp.service_requests.index') }}" method="GET" class="d-flex gap-2">
                @if(request('attended'))
                    <input type="hidden" name="attended" value="{{ request('attended') }}">
                @endif
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                    </span>
                    <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" 
                           placeholder="Search ticket #, customer, PIN..." value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn btn-primary fw-semibold px-3">Search</button>
            </form>
        </div>
    </div>
</div>

<!-- Requests Table -->
<div class="card-dsp overflow-hidden">
    <div class="table-responsive">
        <table class="table table-dsp table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Ticket</th>
                    <th>Customer & Address</th>
                    <th>Problem Description</th>
                    <th>Attendance Status</th>
                    <th>Overall Status</th>
                    <th class="pe-4 text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    @php
                        $customerName = $req->customer_name ?: ($req->user->name ?? 'Customer');
                        $customerPhone = $req->customer_phone ?: ($req->user->phone ?? 'N/A');
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <span class="badge bg-primary-subtle text-primary font-monospace fw-bold fs-12 d-block mb-1">
                                #{{ $req->ticket_number }}
                            </span>
                            @if($req->priority === 'high')
                                <span class="badge bg-danger-subtle text-danger micro fw-bold">HIGH PRIORITY</span>
                            @else
                                <span class="badge bg-light text-muted border micro">NORMAL</span>
                            @endif
                            <div class="text-muted micro mt-1">{{ $req->created_at->format('d M, Y') }}</div>
                        </td>

                        <td>
                            <strong class="text-dark d-block fs-14">{{ $customerName }}</strong>
                            <a href="tel:{{ $customerPhone }}" class="text-decoration-none text-primary micro d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:phone-bold"></iconify-icon>
                                {{ $customerPhone }}
                            </a>
                            <div class="text-secondary micro mt-1">
                                <iconify-icon icon="solar:map-point-bold" class="text-danger align-middle"></iconify-icon>
                                {{ Str::limit($req->address ?: 'Address on file', 30) }}, {{ $req->city }} - <strong>{{ $req->pincode }}</strong>
                            </div>
                        </td>

                        <td>
                            <span class="badge bg-light text-dark border micro mb-1 text-uppercase">
                                {{ ucfirst(str_replace('_', ' ', $req->service_type)) }}
                            </span>
                            <strong class="text-dark d-block fs-13">{{ Str::limit($req->subject, 35) }}</strong>
                            <span class="text-muted micro d-block">{{ Str::limit($req->description, 50) }}</span>
                            @if($req->attachments && count($req->attachments) > 0)
                                <span class="badge bg-secondary-subtle text-secondary micro mt-1">
                                    <iconify-icon icon="solar:paperclip-2-bold" class="align-middle"></iconify-icon>
                                    {{ count($req->attachments) }} Attachment(s)
                                </span>
                            @endif
                        </td>

                        <td>
                            @if($req->is_attended)
                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-12 fw-bold d-inline-flex align-items-center gap-1">
                                    <iconify-icon icon="solar:check-circle-bold" class="fs-6"></iconify-icon>
                                    ATTENDED
                                </span>
                                <div class="micro text-muted mt-1">
                                    {{ $req->attended_at ? $req->attended_at->format('d M, Y H:i') : 'Attended' }}
                                </div>
                                @if($req->attended_by_name)
                                    <div class="micro text-dark fw-semibold mt-1">
                                        Tech: {{ $req->attended_by_name }}
                                    </div>
                                @endif
                            @else
                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12 fw-bold d-inline-flex align-items-center gap-1">
                                    <iconify-icon icon="solar:clock-circle-bold" class="fs-6"></iconify-icon>
                                    ATTENTION REQUIRED
                                </span>
                                <div class="micro text-danger mt-1">Please mark attended</div>
                            @endif
                        </td>

                        <td>
                            @if($req->status === 'resolved')
                                <span class="badge bg-success text-white px-2 py-1 fw-bold fs-12">
                                    RESOLVED
                                </span>
                            @elseif($req->status === 'in_progress')
                                <span class="badge bg-warning text-dark px-2 py-1 fw-bold fs-12">
                                    IN PROGRESS
                                </span>
                            @else
                                <span class="badge bg-danger text-white px-2 py-1 fw-bold fs-12">
                                    OPEN
                                </span>
                            @endif
                        </td>

                        <td class="pe-4 text-end">
                            <a href="{{ route('dsp.service_requests.show', $req->id) }}" class="btn btn-sm btn-primary fw-semibold px-3">
                                <iconify-icon icon="solar:pen-new-square-bold" class="align-middle me-1"></iconify-icon>
                                Attend & Manage
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <iconify-icon icon="solar:inbox-line-bold" class="fs-1 text-secondary opacity-50 mb-2"></iconify-icon>
                            <div>No service requests found for this filter in your territory.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($requests->hasPages())
        <div class="p-3 border-top">
            {{ $requests->links() }}
        </div>
    @endif
</div>
@endsection
