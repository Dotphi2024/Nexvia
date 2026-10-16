@extends('adminlayouts.vertical', ['title' => 'Website Popup Leads'])

@section('title', 'Website Popup Leads & Enquiry Management')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <iconify-icon icon="solar:chat-round-call-bold-duotone" class="align-middle me-1 text-primary fs-22"></iconify-icon>
                Website Popup Leads & Enquiries
            </h4>
            <p class="text-muted small mb-0">Track and manage customer callbacks captured via the 5–10s timed popup on the frontend website.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#popupSettingsModal">
                <iconify-icon icon="solar:settings-minimalistic-outline" class="fs-18"></iconify-icon>
                <span>Popup Settings</span>
            </button>
            <a href="{{ route('admin.popup_leads.export', request()->query()) }}" class="btn btn-success d-inline-flex align-items-center gap-2 shadow-sm">
                <iconify-icon icon="solar:file-download-outline" class="fs-18"></iconify-icon>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:check-circle-bold" class="fs-20 text-success"></iconify-icon>
                <div><strong>Success!</strong> {{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:danger-triangle-bold" class="fs-20 text-danger"></iconify-icon>
                <div>
                    <strong>Please check errors:</strong>
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Quick Metrics KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border border-light-subtle shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Total Leads</div>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <h3 class="fw-bold text-dark mb-0">{{ number_format($stats['total']) }}</h3>
                        <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <iconify-icon icon="solar:users-group-rounded-linear" class="fs-20"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border border-light-subtle shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Today's Leads</div>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <h3 class="fw-bold text-info mb-0">{{ number_format($stats['today']) }}</h3>
                        <div class="avatar-sm bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <iconify-icon icon="solar:calendar-date-outline" class="fs-20"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border border-light-subtle shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Pending / New</div>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <h3 class="fw-bold text-warning mb-0">{{ number_format($stats['pending']) }}</h3>
                        <div class="avatar-sm bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <iconify-icon icon="solar:clock-circle-outline" class="fs-20"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border border-light-subtle shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Contacted</div>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <h3 class="fw-bold text-primary mb-0">{{ number_format($stats['contacted']) }}</h3>
                        <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <iconify-icon icon="solar:phone-calling-outline" class="fs-20"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border border-light-subtle shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Converted</div>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <h3 class="fw-bold text-success mb-0">{{ number_format($stats['converted']) }}</h3>
                        <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <iconify-icon icon="solar:cup-star-outline" class="fs-20"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border border-light-subtle shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="text-muted small fw-semibold text-uppercase">Popup Status</div>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <div>
                            @if($settings->is_enabled)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                    ● Active ({{ $settings->delay_seconds }}s)
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill">
                                    ○ Disabled
                                </span>
                            @endif
                        </div>
                        <div class="avatar-sm bg-light text-muted rounded-circle d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <iconify-icon icon="solar:shield-warning-outline" class="fs-20"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card border border-light-subtle shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.popup_leads.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0">
                            <iconify-icon icon="solar:magnifer-outline"></iconify-icon>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search name, phone, email, notes..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending / New</option>
                        <option value="contacted" {{ request('status') === 'contacted' ? 'selected' : '' }}>Contacted</option>
                        <option value="interested" {{ request('status') === 'interested' ? 'selected' : '' }}>Interested</option>
                        <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Converted / Won</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" placeholder="From Date" value="{{ request('date_from') }}" title="From Date">
                </div>

                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" placeholder="To Date" value="{{ request('date_to') }}" title="To Date">
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100">
                        <iconify-icon icon="solar:filter-linear" class="align-middle me-1"></iconify-icon> Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                        <a href="{{ route('admin.popup_leads.index') }}" class="btn btn-light border" title="Reset Filters">
                            <iconify-icon icon="solar:restart-linear" class="align-middle"></iconify-icon>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border border-light-subtle shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0">Captured Customer Callback Enquiries</h6>
            <span class="badge bg-light text-dark border">{{ $leads->total() }} Total Enquiries</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 50px;">#</th>
                            <th>Customer Info</th>
                            <th>Mobile Number</th>
                            <th>Page / Source</th>
                            <th>Status</th>
                            <th>Admin Follow-up Notes</th>
                            <th>Captured Time</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                            <tr>
                                <td class="ps-3 text-muted fw-bold">{{ $lead->id }}</td>
                                <td>
                                    <div class="fw-bold text-dark fs-14">{{ $lead->name }}</div>
                                    @if($lead->email)
                                        <div class="text-muted small">
                                            <iconify-icon icon="solar:letter-linear" class="align-middle me-1"></iconify-icon>
                                            <a href="mailto:{{ $lead->email }}" class="text-secondary text-decoration-none">{{ $lead->email }}</a>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="tel:{{ $lead->mobile }}" class="btn btn-sm btn-outline-success px-2 py-1 rounded-pill d-inline-flex align-items-center gap-1 text-decoration-none">
                                            <iconify-icon icon="solar:phone-calling-bold"></iconify-icon>
                                            <span class="fw-semibold">{{ $lead->mobile }}</span>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-light border p-1" title="Copy Number" onclick="copyToClipboard('{{ $lead->mobile }}', this)">
                                            <iconify-icon icon="solar:copy-outline" class="fs-14"></iconify-icon>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    @if($lead->source_url)
                                        <span class="text-muted small text-truncate d-inline-block" style="max-width: 180px;" title="{{ $lead->source_url }}">
                                            <iconify-icon icon="solar:link-outline" class="me-1"></iconify-icon>
                                            {{ parse_url($lead->source_url, PHP_URL_PATH) ?: '/' }}
                                        </span>
                                    @else
                                        <span class="text-muted small">Frontend Direct</span>
                                    @endif
                                    @if($lead->ip_address)
                                        <div class="text-muted fs-11">IP: {{ $lead->ip_address }}</div>
                                    @endif
                                </td>
                                <td>
                                    @php $badge = $lead->status_badge; @endphp
                                    <span class="badge {{ $badge['class'] }} rounded-pill px-2 py-1">
                                        {{ $badge['label'] }}
                                    </span>
                                    @if($lead->contacted_at)
                                        <div class="text-muted fs-11 mt-1">Called: {{ $lead->contacted_at->format('M d, H:i') }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($lead->admin_notes)
                                        <span class="small text-dark text-truncate d-inline-block" style="max-width: 220px;" title="{{ $lead->admin_notes }}">
                                            {{ $lead->admin_notes }}
                                        </span>
                                    @else
                                        <span class="text-muted small fst-italic">No remarks recorded</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-dark small fw-medium">{{ $lead->created_at?->format('M d, Y') }}</div>
                                    <div class="text-muted fs-11">{{ $lead->created_at?->format('h:i A') }} ({{ $lead->created_at?->diffForHumans() }})</div>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Edit Status & Notes Button -->
                                        <button type="button" class="btn btn-sm btn-light border text-primary" title="Update Status & Remarks"
                                            onclick="openUpdateModal({{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $lead->mobile }}', '{{ addslashes($lead->email ?? '') }}', '{{ $lead->status }}', '{{ addslashes($lead->admin_notes ?? '') }}')">
                                            <iconify-icon icon="solar:pen-new-square-outline" class="fs-16"></iconify-icon>
                                        </button>

                                        <!-- Delete Lead Button -->
                                        <form action="{{ route('admin.popup_leads.destroy', $lead->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete enquiry lead for {{ addslashes($lead->name) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete Lead">
                                                <iconify-icon icon="solar:trash-bin-trash-outline" class="fs-16"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <iconify-icon icon="solar:chat-round-call-outline" class="fs-48 text-secondary mb-2 d-block mx-auto"></iconify-icon>
                                        <h6 class="fw-bold">No Popup Leads Found</h6>
                                        <p class="small mb-0">Customer requests submitted via the timed popup will appear here.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($leads->hasPages())
            <div class="card-footer bg-white border-top py-3 d-flex justify-content-end">
                {{ $leads->links() }}
            </div>
        @endif
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: UPDATE LEAD STATUS & NOTES -->
<!-- ========================================================================= -->
<div class="modal fade" id="updateLeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="updateLeadForm" method="POST" action="">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark">
                        <iconify-icon icon="solar:user-speak-bold-duotone" class="text-primary me-1 align-middle"></iconify-icon>
                        Update Lead Status & Follow-up
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="fw-bold text-dark" id="modalCustomerName">Customer Name</div>
                        <div class="text-muted small">Mobile: <strong class="text-dark" id="modalCustomerMobile">+91 ...</strong></div>
                        <div class="text-muted small" id="modalCustomerEmailWrapper" style="display: none;">Email: <strong class="text-dark" id="modalCustomerEmail">...</strong></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Lead Status *</label>
                        <select name="status" id="modalLeadStatus" class="form-select" required>
                            <option value="pending">Pending / New (Needs Call)</option>
                            <option value="contacted">Contacted (Called Customer)</option>
                            <option value="interested">Interested (Wants Offer / Quote)</option>
                            <option value="converted">Converted / Won (Booked / Purchased)</option>
                            <option value="closed">Closed / Not Interested</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Admin Remarks & Call Notes</label>
                        <textarea name="admin_notes" id="modalAdminNotes" class="form-control" rows="4" placeholder="e.g. Called customer at 2 PM. Interested in 55-inch LED TV 20% booking scheme. Requested callback tomorrow..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: POPUP CONFIGURATION & TIMING SETTINGS -->
<!-- ========================================================================= -->
<div class="modal fade" id="popupSettingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.popup_leads.settings.update') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark">
                        <iconify-icon icon="solar:settings-bold-duotone" class="text-primary me-1 align-middle"></iconify-icon>
                        Configure Frontend Website Timed Popup
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-info-subtle bg-info-subtle text-dark small rounded-3 mb-4">
                        <iconify-icon icon="solar:info-circle-bold" class="fs-18 text-info align-middle me-1"></iconify-icon>
                        Control popup visibility, timing delay (e.g. 5 to 10 seconds), headings, and CTA button text dynamically on the live frontend website.
                    </div>

                    <div class="row g-3">
                        <!-- Enable / Disable Switch -->
                        <div class="col-12">
                            <div class="form-check form-switch p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between ps-3">
                                <div>
                                    <label class="form-check-label fw-bold text-dark mb-0" for="popupSwitch">
                                        Enable Timed Popup on Website
                                    </label>
                                    <div class="text-muted small">When turned on, visitors see this lead-capture modal after the specified delay.</div>
                                </div>
                                <input class="form-check-input fs-20 ms-auto" type="checkbox" name="is_enabled" value="1" id="popupSwitch" {{ $settings->is_enabled ? 'checked' : '' }}>
                            </div>
                        </div>

                        <!-- Timing Delay -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Trigger Delay (in Seconds) *</label>
                            <div class="input-group">
                                <input type="number" name="delay_seconds" class="form-control" min="1" max="120" value="{{ $settings->delay_seconds ?? 7 }}" required>
                                <span class="input-group-text bg-light text-muted">seconds</span>
                            </div>
                            <div class="form-text text-muted">Recommended: <strong>5 to 10 seconds</strong> after visitor opens page.</div>
                        </div>

                        <!-- Reappear After (Hours) -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Dismiss Reappear Frequency *</label>
                            <div class="input-group">
                                <input type="number" name="reappear_hours" class="form-control" min="0" max="720" value="{{ $settings->reappear_hours ?? 24 }}" required>
                                <span class="input-group-text bg-light text-muted">hours</span>
                            </div>
                            <div class="form-text text-muted">Hours to wait before showing popup again if dismissed (0 = every visit).</div>
                        </div>

                        <!-- Popup Heading Title -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Popup Title / Headline *</label>
                            <input type="text" name="title" class="form-control" value="{{ $settings->title }}" required placeholder="e.g. Get Exclusive Offers & Instant Callback">
                        </div>

                        <!-- Subtitle -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Subtitle / Offer Description</label>
                            <textarea name="subtitle" class="form-control" rows="2" placeholder="e.g. Enter your name and mobile number to receive exclusive pricing and product support.">{{ $settings->subtitle }}</textarea>
                        </div>

                        <!-- Button Text -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Submit Button Text *</label>
                            <input type="text" name="button_text" class="form-control" value="{{ $settings->button_text }}" required placeholder="e.g. Request Callback Now">
                        </div>

                        <!-- Success Message -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Submission Success Message</label>
                            <input type="text" name="success_message" class="form-control" value="{{ $settings->success_message }}" placeholder="e.g. Thank you! We will call you shortly.">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4">Save Configuration</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openUpdateModal(id, name, mobile, email, status, notes) {
    document.getElementById('modalCustomerName').textContent = name;
    document.getElementById('modalCustomerMobile').textContent = mobile;
    
    var emailWrapper = document.getElementById('modalCustomerEmailWrapper');
    var emailElem = document.getElementById('modalCustomerEmail');
    if (email && email.trim() !== '') {
        emailElem.textContent = email;
        emailWrapper.style.display = 'block';
    } else {
        emailWrapper.style.display = 'none';
    }

    document.getElementById('modalLeadStatus').value = status;
    document.getElementById('modalAdminNotes').value = notes;
    
    // Construct route URL
    document.getElementById('updateLeadForm').action = "{{ url('/admin/popup-leads') }}/" + id + "/status";
    
    var modal = new bootstrap.Modal(document.getElementById('updateLeadModal'));
    modal.show();
}

function copyToClipboard(text, btnElement) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(showCopiedFeedback).catch(fallback);
    } else {
        fallback();
    }
    
    function fallback() {
        var textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            showCopiedFeedback();
        } catch (err) {
            console.error('Fallback copy failed', err);
        }
        document.body.removeChild(textArea);
    }

    function showCopiedFeedback() {
        var origHtml = btnElement.innerHTML;
        btnElement.innerHTML = '<iconify-icon icon="solar:check-circle-bold" class="fs-14 text-success"></iconify-icon>';
        setTimeout(function() {
            btnElement.innerHTML = origHtml;
        }, 1500);
    }
}
</script>
@endsection
