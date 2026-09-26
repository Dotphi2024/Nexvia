@extends('adminlayouts.vertical', ['title' => 'DSP Service & Problem Requests'])

@section('title', 'DSP Service & Problem Requests Management')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <span class="bg-primary text-white p-2 rounded-3 d-inline-flex">
                    <iconify-icon icon="solar:wrench-bold-duotone" class="fs-4"></iconify-icon>
                </span>
                DSP Service & Problem Requests
            </h4>
            <p class="text-muted small mb-0">Monitor service tickets, technician attendance, territory DSP allocation, and resolution proofs</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border px-3 py-2 fs-13">
                <iconify-icon icon="solar:users-group-two-rounded-bold" class="text-primary me-1 align-middle"></iconify-icon>
                {{ $dsps->count() }} Active Delivery & Service Partners
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:check-circle-bold" class="fs-4 text-success"></iconify-icon>
                <div><strong>Success!</strong> {{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:danger-triangle-bold" class="fs-4 text-danger"></iconify-icon>
                <div><strong>Attention!</strong> {{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Metric Cards -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-2dot4 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Total Requests</span>
                    <span class="badge bg-primary-subtle text-primary rounded-pill p-2">
                        <iconify-icon icon="solar:bill-list-bold" class="fs-5"></iconify-icon>
                    </span>
                </div>
                <h3 class="fw-bold text-dark mb-0">{{ number_format($stats['total'] ?? 0) }}</h3>
                <span class="text-muted micro">All registered tickets</span>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2dot4 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Assigned to DSP</span>
                    <span class="badge bg-info-subtle text-info rounded-pill p-2">
                        <iconify-icon icon="solar:routing-bold" class="fs-5"></iconify-icon>
                    </span>
                </div>
                <h3 class="fw-bold text-info mb-0">{{ number_format($stats['assigned_dsp'] ?? 0) }}</h3>
                <span class="text-muted micro">{{ $stats['unassigned'] ?? 0 }} unassigned</span>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2dot4 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-success small fw-bold">Attended by DSP</span>
                    <span class="badge bg-success-subtle text-success rounded-pill p-2">
                        <iconify-icon icon="solar:check-circle-bold" class="fs-5"></iconify-icon>
                    </span>
                </div>
                <h3 class="fw-bold text-success mb-0">{{ number_format($stats['attended'] ?? 0) }}</h3>
                <span class="text-muted micro">DSP or Tech attended</span>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2dot4 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-warning-emphasis small fw-bold">Pending Attention</span>
                    <span class="badge bg-warning-subtle text-warning rounded-pill p-2">
                        <iconify-icon icon="solar:clock-circle-bold" class="fs-5"></iconify-icon>
                    </span>
                </div>
                <h3 class="fw-bold text-warning-emphasis mb-0">{{ number_format($stats['pending_attention'] ?? 0) }}</h3>
                <span class="text-muted micro">DSP not attended yet</span>
            </div>
        </div>

        <div class="col-sm-6 col-xl-2dot4 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Resolved</span>
                    <span class="badge bg-secondary-subtle text-secondary rounded-pill p-2">
                        <iconify-icon icon="solar:shield-check-bold" class="fs-5"></iconify-icon>
                    </span>
                </div>
                <h3 class="fw-bold text-secondary mb-0">{{ number_format($stats['resolved'] ?? 0) }}</h3>
                <span class="text-muted micro">Fully closed tickets</span>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.service.requests.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-lg-3 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                        </span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" 
                               placeholder="Search ticket, customer, PIN, DSP..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6">
                    <select name="attended" class="form-select">
                        <option value="">-- Attendance: All --</option>
                        <option value="yes" {{ request('attended') === 'yes' ? 'selected' : '' }}>Attended by DSP</option>
                        <option value="no" {{ request('attended') === 'no' ? 'selected' : '' }}>Pending Attention</option>
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6">
                    <select name="status" class="form-select">
                        <option value="">-- Status: All --</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <select name="dsp_id" class="form-select">
                        <option value="">-- DSP Partner: All --</option>
                        <option value="unassigned" {{ request('dsp_id') === 'unassigned' ? 'selected' : '' }}>⚠️ Unassigned (No DSP)</option>
                        @foreach($dsps as $dsp)
                            <option value="{{ $dsp->id }}" {{ request('dsp_id') == $dsp->id ? 'selected' : '' }}>
                                {{ $dsp->business_name }} ({{ $dsp->district }}, {{ $dsp->premises_pincode }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        <iconify-icon icon="solar:filter-bold" class="align-middle me-1"></iconify-icon> Filter
                    </button>
                    @if(request()->anyFilled(['search', 'attended', 'status', 'dsp_id', 'service_type']))
                        <a href="{{ route('admin.service.requests.index') }}" class="btn btn-outline-secondary" title="Reset Filters">
                            <iconify-icon icon="solar:restart-bold" class="align-middle"></iconify-icon>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table of Service Requests -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 140px;">Ticket #</th>
                        <th>Customer & Location</th>
                        <th>Subject & Issue</th>
                        <th>Allocated DSP Partner</th>
                        <th style="min-width: 180px;">DSP Attendance</th>
                        <th>Status</th>
                        <th class="pe-4 text-end" style="min-width: 160px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        @php
                            $customerName = $req->customer_name ?: ($req->user->name ?? 'Guest Customer');
                            $customerPhone = $req->customer_phone ?: ($req->user->phone ?? 'N/A');
                            $locationStr = array_filter([$req->city, $req->state, $req->pincode ? "PIN: {$req->pincode}" : null]);
                            $locationText = implode(', ', $locationStr);
                        @endphp
                        <tr>
                            <!-- Ticket Number & Priority -->
                            <td class="ps-4">
                                <span class="badge bg-primary-subtle text-primary font-monospace fw-bold fs-12 d-block mb-1">
                                    #{{ $req->ticket_number }}
                                </span>
                                @if($req->priority === 'high')
                                    <span class="badge bg-danger-subtle text-danger micro fw-bold">HIGH PRIORITY</span>
                                @elseif($req->priority === 'low')
                                    <span class="badge bg-secondary-subtle text-secondary micro">LOW</span>
                                @else
                                    <span class="badge bg-info-subtle text-info micro">MEDIUM</span>
                                @endif
                                <div class="text-muted micro mt-1">{{ $req->created_at->format('d M, Y') }}</div>
                            </td>

                            <!-- Customer & Location -->
                            <td>
                                <strong class="text-dark d-block fs-14">{{ $customerName }}</strong>
                                <span class="text-muted micro d-flex align-items-center gap-1">
                                    <iconify-icon icon="solar:phone-bold" class="text-secondary"></iconify-icon>
                                    {{ $customerPhone }}
                                </span>
                                @if($locationText)
                                    <span class="text-secondary micro d-flex align-items-center gap-1 mt-1">
                                        <iconify-icon icon="solar:map-point-bold" class="text-danger"></iconify-icon>
                                        {{ Str::limit($locationText, 28) }}
                                    </span>
                                @endif
                                @if($req->booking)
                                    <span class="badge bg-light text-muted border micro mt-1">
                                        Order #{{ $req->booking->booking_number }}
                                    </span>
                                @endif
                            </td>

                            <!-- Subject & Issue -->
                            <td>
                                <span class="badge bg-light text-dark border micro mb-1 text-uppercase">
                                    {{ ucfirst(str_replace('_', ' ', $req->service_type)) }}
                                </span>
                                <strong class="text-dark d-block fs-13">{{ Str::limit($req->subject, 35) }}</strong>
                                <span class="text-muted micro d-block">
                                    {{ Str::limit($req->description, 50) }}
                                </span>
                                @if($req->attachments && count($req->attachments) > 0)
                                    <span class="badge bg-secondary-subtle text-secondary micro mt-1">
                                        <iconify-icon icon="solar:paperclip-2-bold" class="align-middle"></iconify-icon>
                                        {{ count($req->attachments) }} File(s)
                                    </span>
                                @endif
                            </td>

                            <!-- Allocated DSP Partner -->
                            <td>
                                @if($req->dsp)
                                    <div class="d-flex align-items-start gap-2">
                                        <span class="bg-primary-subtle text-primary p-2 rounded-3 d-inline-flex mt-1">
                                            <iconify-icon icon="solar:delivery-bold" class="fs-5"></iconify-icon>
                                        </span>
                                        <div>
                                            <strong class="text-dark d-block fs-13">{{ $req->dsp->business_name }}</strong>
                                            <span class="text-muted micro d-block">{{ $req->dsp->applicant_name }} &bull; {{ $req->dsp->mobile }}</span>
                                            <span class="badge bg-light text-secondary border micro mt-1">
                                                Territory: {{ $req->dsp->district }}, {{ $req->dsp->state }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-warning-emphasis">
                                        <span class="badge bg-warning-subtle text-warning-emphasis fw-bold px-2 py-1">
                                            <iconify-icon icon="solar:danger-triangle-bold" class="align-middle me-1"></iconify-icon>
                                            NOT ALLOCATED
                                        </span>
                                        <div class="micro text-muted mt-1">Customer Area: {{ $req->pincode ?: 'No PIN' }}</div>
                                    </div>
                                @endif
                            </td>

                            <!-- DSP Attendance Status -->
                            <td>
                                @if($req->is_attended)
                                    <div class="d-flex align-items-start gap-2">
                                        <span class="badge bg-success-subtle text-success px-2 py-1 fs-12 fw-bold d-inline-flex align-items-center gap-1">
                                            <iconify-icon icon="solar:check-circle-bold" class="fs-6"></iconify-icon>
                                            ATTENDED
                                        </span>
                                    </div>
                                    <div class="micro text-muted mt-1">
                                        {{ $req->attended_at ? $req->attended_at->format('d M, Y H:i') : 'Attended' }}
                                    </div>
                                    @if($req->attended_by_name)
                                        <div class="micro text-dark fw-semibold mt-1">
                                            Tech: {{ $req->attended_by_name }}
                                            @if($req->attended_by_phone)
                                                ({{ $req->attended_by_phone }})
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12 fw-bold d-inline-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:clock-circle-bold" class="fs-6"></iconify-icon>
                                        PENDING ATTENTION
                                    </span>
                                    <div class="micro text-danger mt-1">
                                        DSP has not attended yet
                                    </div>
                                @endif

                                @if($req->dsp_notes)
                                    <div class="micro text-muted mt-1 font-monospace" title="{{ $req->dsp_notes }}">
                                        Notes: {{ Str::limit($req->dsp_notes, 28) }}
                                    </div>
                                @endif
                            </td>

                            <!-- Overall Status -->
                            <td>
                                @if($req->status === 'resolved')
                                    <span class="badge bg-success text-white px-2 py-1 fw-bold fs-12">
                                        <iconify-icon icon="solar:check-read-bold" class="align-middle me-1"></iconify-icon> RESOLVED
                                    </span>
                                    @if($req->resolved_at)
                                        <div class="micro text-muted mt-1">{{ $req->resolved_at->format('d M, Y') }}</div>
                                    @endif
                                @elseif($req->status === 'in_progress')
                                    <span class="badge bg-warning text-dark px-2 py-1 fw-bold fs-12">
                                        <iconify-icon icon="solar:refresh-bold" class="align-middle me-1"></iconify-icon> IN PROGRESS
                                    </span>
                                @elseif($req->status === 'attended')
                                    <span class="badge bg-info text-white px-2 py-1 fw-bold fs-12">
                                        ATTENDED
                                    </span>
                                @else
                                    <span class="badge bg-danger text-white px-2 py-1 fw-bold fs-12">
                                        OPEN
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="pe-4 text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <!-- View Details Modal Trigger -->
                                    <button type="button" class="btn btn-sm btn-light border" 
                                            data-bs-toggle="modal" data-bs-target="#viewModal{{ $req->id }}" title="View Ticket Details">
                                        <iconify-icon icon="solar:eye-bold" class="align-middle"></iconify-icon>
                                    </button>

                                    <!-- Assign / Reassign DSP Modal Trigger -->
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" data-bs-target="#assignModal{{ $req->id }}" title="Assign or Reallocate DSP">
                                        <iconify-icon icon="solar:user-hand-up-bold" class="align-middle me-1"></iconify-icon>
                                        {{ $req->dsp_id ? 'Reassign' : 'Assign DSP' }}
                                    </button>

                                    <!-- Quick Status Update Dropdown -->
                                    <div class="dropdown d-inline">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Status
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <form action="{{ route('admin.service.requests.status', $req->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="status" value="open">
                                                    <button type="submit" class="dropdown-item py-2 {{ $req->status === 'open' ? 'active' : '' }}">Mark Open</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.service.requests.status', $req->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="status" value="in_progress">
                                                    <input type="hidden" name="is_attended" value="1">
                                                    <button type="submit" class="dropdown-item py-2 {{ $req->status === 'in_progress' ? 'active' : '' }}">Mark In Progress (Attended)</button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.service.requests.status', $req->id) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="status" value="resolved">
                                                    <input type="hidden" name="is_attended" value="1">
                                                    <button type="submit" class="dropdown-item text-success py-2 {{ $req->status === 'resolved' ? 'active' : '' }}">Mark Fully Resolved</button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal 1: Inspect Ticket Details -->
                        <div class="modal fade" id="viewModal{{ $req->id }}" tabindex="-1" aria-labelledby="viewModalLabel{{ $req->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-bottom bg-light">
                                        <div>
                                            <h5 class="modal-title fw-bold text-dark" id="viewModalLabel{{ $req->id }}">
                                                Service Request #{{ $req->ticket_number }}
                                            </h5>
                                            <span class="micro text-muted">Created: {{ $req->created_at->format('d M, Y H:i A') }}</span>
                                        </div>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body p-4">
                                        <!-- Customer & Location Card -->
                                        <div class="card bg-light border-0 rounded-3 p-3 mb-3">
                                            <h6 class="fw-bold text-dark mb-2 d-flex align-items-center gap-2">
                                                <iconify-icon icon="solar:user-bold" class="text-primary"></iconify-icon>
                                                Customer & Location Details
                                            </h6>
                                            <div class="row g-2">
                                                <div class="col-sm-6">
                                                    <span class="micro text-muted d-block">Customer Name</span>
                                                    <strong class="text-dark">{{ $customerName }}</strong>
                                                </div>
                                                <div class="col-sm-6">
                                                    <span class="micro text-muted d-block">Phone Number</span>
                                                    <strong class="text-dark">{{ $customerPhone }}</strong>
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <span class="micro text-muted d-block">Service Address & Pincode</span>
                                                    <div class="text-dark fw-medium">
                                                        {{ $req->address ?: 'N/A' }}, {{ $req->city }}, {{ $req->state }} - <strong>{{ $req->pincode }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Problem Summary -->
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="text-muted micro text-uppercase fw-bold">Service Type</span>
                                                <span class="badge bg-primary-subtle text-primary">{{ ucfirst($req->service_type) }}</span>
                                            </div>
                                            <h6 class="fw-bold text-dark">{{ $req->subject }}</h6>
                                            <div class="p-3 bg-light rounded-3 text-secondary" style="white-space: pre-wrap;">{{ $req->description }}</div>
                                        </div>

                                        <!-- Customer Uploaded Attachments -->
                                        @if($req->attachments && count($req->attachments) > 0)
                                            <div class="mb-3">
                                                <span class="text-muted micro text-uppercase fw-bold d-block mb-2">Customer Uploaded Photos / Documents</span>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($req->attachments as $att)
                                                        @php
                                                            $attUrl = asset($att);
                                                            $isImg = preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $att);
                                                        @endphp
                                                        @if($isImg)
                                                            <a href="{{ $attUrl }}" target="_blank" class="d-block border rounded-3 p-1 bg-white">
                                                                <img src="{{ $attUrl }}" style="width: 80px; height: 80px; object-fit: cover;" class="rounded" alt="attachment">
                                                            </a>
                                                        @else
                                                            <a href="{{ $attUrl }}" target="_blank" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                                                                <iconify-icon icon="solar:document-bold"></iconify-icon> View Attachment
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <hr class="my-3">

                                        <!-- DSP Partner & Attendance Information -->
                                        <div class="card border rounded-3 p-3 mb-3 {{ $req->is_attended ? 'border-success bg-success-subtle bg-opacity-10' : 'border-warning bg-warning-subtle bg-opacity-10' }}">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                                    <iconify-icon icon="solar:delivery-bold" class="text-primary fs-5"></iconify-icon>
                                                    Allocated DSP Partner & Attendance Record
                                                </h6>
                                                @if($req->is_attended)
                                                    <span class="badge bg-success px-2 py-1">
                                                        <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon>
                                                        ATTENDED
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger px-2 py-1">
                                                        <iconify-icon icon="solar:clock-circle-bold" class="align-middle me-1"></iconify-icon>
                                                        NOT ATTENDED
                                                    </span>
                                                @endif
                                            </div>

                                            @if($req->dsp)
                                                <div class="row g-2">
                                                    <div class="col-sm-6">
                                                        <span class="micro text-muted d-block">DSP Partner Firm</span>
                                                        <strong class="text-dark">{{ $req->dsp->business_name }}</strong>
                                                        <div class="micro text-muted">{{ $req->dsp->applicant_name }} &bull; {{ $req->dsp->mobile }}</div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <span class="micro text-muted d-block">Attended Timestamp</span>
                                                        <div class="text-dark fw-medium">
                                                            {{ $req->attended_at ? $req->attended_at->format('d M, Y h:i A') : 'Pending Partner Action' }}
                                                        </div>
                                                    </div>
                                                    @if($req->attended_by_name)
                                                        <div class="col-sm-6 mt-2">
                                                            <span class="micro text-muted d-block">Technician Assigned</span>
                                                            <strong class="text-dark">{{ $req->attended_by_name }}</strong> 
                                                            @if($req->attended_by_phone)
                                                                <span class="text-muted">({{ $req->attended_by_phone }})</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                    @if($req->dsp_notes)
                                                        <div class="col-12 mt-2">
                                                            <span class="micro text-muted d-block">DSP Progress Notes</span>
                                                            <div class="bg-white p-2 rounded border small text-dark">{{ $req->dsp_notes }}</div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <p class="text-warning-emphasis mb-0 small">
                                                    <iconify-icon icon="solar:danger-triangle-bold" class="align-middle me-1"></iconify-icon>
                                                    No Delivery & Service Partner is currently allocated to this request. Click 'Assign DSP' to dispatch to a local partner.
                                                </p>
                                            @endif
                                        </div>

                                        <!-- Resolution Proof & Notes (if resolved) -->
                                        @if($req->status === 'resolved' || $req->resolution_notes || $req->resolution_proof)
                                            <div class="card bg-light border-0 rounded-3 p-3">
                                                <h6 class="fw-bold text-success mb-2 d-flex align-items-center gap-2">
                                                    <iconify-icon icon="solar:shield-check-bold" class="text-success"></iconify-icon>
                                                    Resolution Summary
                                                </h6>
                                                @if($req->resolved_at)
                                                    <div class="micro text-muted mb-2">Resolved On: {{ $req->resolved_at->format('d M, Y h:i A') }}</div>
                                                @endif
                                                @if($req->resolution_notes)
                                                    <div class="small text-dark mb-2">
                                                        <strong>Notes:</strong> {{ $req->resolution_notes }}
                                                    </div>
                                                @endif
                                                @if($req->resolution_proof)
                                                    <div>
                                                        <span class="micro text-muted d-block mb-1">Resolution Proof:</span>
                                                        <a href="{{ asset($req->resolution_proof) }}" target="_blank" class="d-inline-block border rounded p-1 bg-white">
                                                            <img src="{{ asset($req->resolution_proof) }}" style="max-height: 120px;" class="rounded" alt="resolution proof">
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <div class="modal-footer border-top bg-light">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#assignModal{{ $req->id }}">
                                            Manage DSP Allocation
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal 2: Assign / Reassign DSP Partner -->
                        <div class="modal fade" id="assignModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-bottom bg-light">
                                        <h5 class="modal-title fw-bold text-dark">
                                            Allocate DSP Partner – Ticket #{{ $req->ticket_number }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body p-4">
                                        <div class="alert alert-info border-0 rounded-3 micro mb-3">
                                            <strong>Customer Location:</strong> {{ $req->city ?? 'N/A' }}, {{ $req->state ?? 'N/A' }} 
                                            (PIN: <strong>{{ $req->pincode ?? 'None' }}</strong>)
                                        </div>

                                        <!-- Auto Match Button -->
                                        <form action="{{ route('admin.service.requests.assign_dsp', $req->id) }}" method="POST" class="mb-4">
                                            @csrf
                                            <input type="hidden" name="auto_match" value="1">
                                            <button type="submit" class="btn btn-outline-primary w-100 fw-bold py-2 d-flex align-items-center justify-content-center gap-2">
                                                <iconify-icon icon="solar:magic-stick-3-bold" class="fs-5"></iconify-icon>
                                                Auto-Match DSP by Customer Location (PIN: {{ $req->pincode }})
                                            </button>
                                        </form>

                                        <div class="text-center position-relative my-3">
                                            <hr>
                                            <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted micro fw-semibold">
                                                OR SELECT DSP MANUALLY
                                            </span>
                                        </div>

                                        <!-- Manual Select Form -->
                                        <form action="{{ route('admin.service.requests.assign_dsp', $req->id) }}" method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-dark small">Select Authorised DSP Partner *</label>
                                                <select name="dsp_id" class="form-select form-select-lg" required>
                                                    <option value="">-- Choose Approved DSP --</option>
                                                    @foreach($dsps as $dsp)
                                                        <option value="{{ $dsp->id }}" {{ $req->dsp_id == $dsp->id ? 'selected' : '' }}>
                                                            {{ $dsp->business_name }} &bull; {{ $dsp->applicant_name }} ({{ $dsp->district }}, PIN: {{ $dsp->premises_pincode }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <div class="form-text micro">DSP will immediately receive this request in their partner dashboard & API.</div>
                                            </div>

                                            <div class="d-flex justify-content-end gap-2 mt-4">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary fw-bold">Confirm Allocation</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <iconify-icon icon="solar:inbox-line-bold" class="fs-1 text-secondary opacity-50 mb-2"></iconify-icon>
                                <div>No service or problem requests match the current filters.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="p-3 border-top bg-light">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
