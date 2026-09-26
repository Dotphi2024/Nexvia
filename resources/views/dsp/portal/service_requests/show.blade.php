@extends('dsp.layouts.portal')

@section('title', 'Service Request #' . $serviceRequest->ticket_number)

@section('content')
<div class="mb-4">
    <a href="{{ route('dsp.service_requests.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 fw-bold mb-3">
        <iconify-icon icon="solar:arrow-left-bold" class="align-middle me-1"></iconify-icon> Back to Service Requests
    </a>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary text-white font-monospace fs-6 px-3 py-1">
                    #{{ $serviceRequest->ticket_number }}
                </span>
                <span class="badge bg-light text-dark border micro text-uppercase fw-bold">
                    {{ ucfirst(str_replace('_', ' ', $serviceRequest->service_type)) }}
                </span>
                @if($serviceRequest->priority === 'high')
                    <span class="badge bg-danger-subtle text-danger micro fw-bold">HIGH PRIORITY</span>
                @endif
            </div>
            <h4 class="fw-bold text-dark mb-0">{{ $serviceRequest->subject }}</h4>
            <span class="text-muted micro">Reported on {{ $serviceRequest->created_at->format('d M, Y h:i A') }}</span>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($serviceRequest->is_attended)
                <span class="badge bg-success-subtle text-success border border-success border-opacity-25 px-3 py-2 fs-6">
                    <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon>
                    ATTENDED BY PARTNER
                </span>
            @else
                <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-25 px-3 py-2 fs-6">
                    <iconify-icon icon="solar:clock-circle-bold" class="align-middle me-1"></iconify-icon>
                    ATTENTION REQUIRED
                </span>
            @endif

            @if($serviceRequest->status === 'resolved')
                <span class="badge bg-success text-white px-3 py-2 fs-6">RESOLVED</span>
            @elseif($serviceRequest->status === 'in_progress')
                <span class="badge bg-warning text-dark px-3 py-2 fs-6">IN PROGRESS</span>
            @else
                <span class="badge bg-danger text-white px-3 py-2 fs-6">OPEN</span>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Customer & Problem Details -->
    <div class="col-lg-7">
        <!-- Customer & Location Card -->
        <div class="card-dsp p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <iconify-icon icon="solar:user-circle-bold" class="text-primary fs-4"></iconify-icon>
                Customer Contact & Service Location
            </h5>

            @php
                $cName = $serviceRequest->customer_name ?: ($serviceRequest->user->name ?? 'Customer');
                $cPhone = $serviceRequest->customer_phone ?: ($serviceRequest->user->phone ?? 'N/A');
                $fullAddress = trim(($serviceRequest->address ? $serviceRequest->address . ', ' : '') . $serviceRequest->city . ', ' . $serviceRequest->state . ' - ' . $serviceRequest->pincode, ', -');
                $mapQuery = urlencode($fullAddress);
            @endphp

            <div class="bg-light rounded-3 p-3 mb-3">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <span class="micro text-muted d-block">Customer Name</span>
                        <strong class="text-dark fs-6">{{ $cName }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="micro text-muted d-block">Contact Phone</span>
                        <a href="tel:{{ $cPhone }}" class="btn btn-sm btn-outline-primary fw-bold mt-1 d-inline-flex align-items-center gap-1">
                            <iconify-icon icon="solar:phone-bold"></iconify-icon> Call {{ $cPhone }}
                        </a>
                    </div>
                    <div class="col-12 border-top pt-2">
                        <span class="micro text-muted d-block">Territory Service Address</span>
                        <div class="text-dark fw-medium mt-1">
                            <iconify-icon icon="solar:map-point-bold" class="text-danger align-middle me-1"></iconify-icon>
                            {{ $fullAddress ?: 'Location not specified' }}
                        </div>
                        @if($serviceRequest->pincode)
                            <a href="https://maps.google.com/?q={{ $mapQuery }}" target="_blank" class="btn btn-sm btn-light border mt-2 micro fw-semibold">
                                <iconify-icon icon="solar:routing-bold" class="text-primary align-middle me-1"></iconify-icon> Open in Google Maps
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            @if($serviceRequest->booking)
                <div class="border rounded-3 p-3 bg-white">
                    <span class="micro text-muted d-block fw-semibold text-uppercase">Associated Product Booking</span>
                    <div class="d-flex justify-content-between align-items-center mt-1">
                        <div>
                            <strong class="text-dark">Order #{{ $serviceRequest->booking->booking_number }}</strong>
                            <div class="micro text-muted">{{ $serviceRequest->booking->product->title ?? 'Product' }}</div>
                        </div>
                        <span class="badge bg-light text-dark border">
                            Purchased: {{ $serviceRequest->booking->created_at->format('d M, Y') }}
                        </span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Problem Description Card -->
        <div class="card-dsp p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <iconify-icon icon="solar:danger-triangle-bold" class="text-warning fs-4"></iconify-icon>
                Customer's Problem Description
            </h5>

            <div class="p-3 bg-light rounded-3 text-secondary mb-3" style="white-space: pre-wrap; font-size: 0.95rem; line-height: 1.6;">
                {{ $serviceRequest->description }}
            </div>

            @if($serviceRequest->attachments && count($serviceRequest->attachments) > 0)
                <h6 class="fw-bold text-dark micro text-uppercase mb-2">Customer Uploaded Photos / Documents</h6>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($serviceRequest->attachments as $att)
                        @php
                            $attUrl = asset($att);
                            $isImg = preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $att);
                        @endphp
                        @if($isImg)
                            <a href="{{ $attUrl }}" target="_blank" class="d-block border rounded-3 p-1 bg-white hover-shadow">
                                <img src="{{ $attUrl }}" style="width: 100px; height: 100px; object-fit: cover;" class="rounded" alt="attachment">
                            </a>
                        @else
                            <a href="{{ $attUrl }}" target="_blank" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                                <iconify-icon icon="solar:document-bold"></iconify-icon> View Attachment
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Right Column: DSP Attendance & Resolution Action Form -->
    <div class="col-lg-5">
        <div class="card-dsp p-4 mb-4 border-primary">
            <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <iconify-icon icon="solar:shield-check-bold" class="text-primary fs-4"></iconify-icon>
                Update Attendance & Status
            </h5>
            <p class="text-muted micro mb-3">Maintain service attendance, assign technician, and record resolution for Admin and Customer tracking.</p>

            <form action="{{ route('dsp.service_requests.status', $serviceRequest->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Attendance Toggle -->
                <div class="form-check form-switch p-3 bg-light rounded-3 mb-3">
                    <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="attendedSwitch" 
                           name="is_attended" value="1" {{ $serviceRequest->is_attended ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-dark ms-1" for="attendedSwitch">
                        Mark as Attended by DSP
                    </label>
                    <div class="micro text-muted mt-1 ps-1">
                        Turn this ON when you or your technician have contacted/visited the customer.
                        @if($serviceRequest->attended_at)
                            <div class="text-success mt-1">First attended on: {{ $serviceRequest->attended_at->format('d M, Y h:i A') }}</div>
                        @endif
                    </div>
                </div>

                <!-- Technician Details -->
                <div class="row g-2 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold text-dark small">Attending Technician Name</label>
                        <input type="text" name="attended_by_name" class="form-control" 
                               placeholder="e.g. Ramesh Kumar" value="{{ old('attended_by_name', $serviceRequest->attended_by_name) }}">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold text-dark small">Technician Mobile</label>
                        <input type="text" name="attended_by_phone" class="form-control" 
                               placeholder="e.g. 9876543210" value="{{ old('attended_by_phone', $serviceRequest->attended_by_phone) }}">
                    </div>
                </div>

                <!-- Status Selector -->
                <div class="mb-3">
                    <label class="form-label fw-bold text-dark small">Overall Service Status *</label>
                    <select name="status" id="statusSelect" class="form-select form-select-lg fw-semibold" required>
                        <option value="open" {{ $serviceRequest->status === 'open' ? 'selected' : '' }}>
                            Open (Under Review)
                        </option>
                        <option value="attended" {{ $serviceRequest->status === 'attended' ? 'selected' : '' }}>
                            Attended (Customer Contacted / Scheduled)
                        </option>
                        <option value="in_progress" {{ $serviceRequest->status === 'in_progress' ? 'selected' : '' }}>
                            In Progress (Technician Working / Part Ordered)
                        </option>
                        <option value="resolved" {{ $serviceRequest->status === 'resolved' ? 'selected' : '' }}>
                            Resolved (Work Completed & Tested)
                        </option>
                    </select>
                </div>

                <!-- DSP Notes -->
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark small">DSP Territory Notes & Updates</label>
                    <textarea name="dsp_notes" class="form-control" rows="3" 
                              placeholder="Add notes for Admin and logs (e.g. scheduled inspection for tomorrow 2 PM)">{{ old('dsp_notes', $serviceRequest->dsp_notes) }}</textarea>
                </div>

                <!-- Resolution Section (Show always if resolved, or optional) -->
                <div class="border rounded-3 p-3 bg-light mb-4" id="resolutionBox">
                    <h6 class="fw-bold text-dark micro text-uppercase mb-2 d-flex align-items-center gap-1">
                        <iconify-icon icon="solar:check-circle-bold" class="text-success"></iconify-icon>
                        Resolution Details (When Closing Ticket)
                    </h6>
                    <div class="mb-2">
                        <label class="form-label micro text-muted mb-1">Resolution Summary / Action Taken</label>
                        <textarea name="resolution_notes" class="form-control form-control-sm" rows="2" 
                                  placeholder="What was fixed, replaced, or explained to customer?">{{ old('resolution_notes', $serviceRequest->resolution_notes) }}</textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label micro text-muted mb-1">Upload Work / Resolution Proof (Photo or Signed Job Card)</label>
                        <input type="file" name="resolution_proof" class="form-control form-control-sm" accept="image/*,.pdf">
                    </div>
                    @if($serviceRequest->resolution_proof)
                        <div class="mt-2">
                            <span class="micro text-muted d-block">Existing Proof:</span>
                            <a href="{{ asset($serviceRequest->resolution_proof) }}" target="_blank" class="d-inline-block border rounded p-1 bg-white">
                                <img src="{{ asset($serviceRequest->resolution_proof) }}" style="max-height: 80px;" class="rounded" alt="proof">
                            </a>
                        </div>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                    <iconify-icon icon="solar:diskette-bold" class="align-middle me-1"></iconify-icon> Save Status & Updates
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
