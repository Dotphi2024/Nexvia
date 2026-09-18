@extends('adminlayouts.vertical', ['title' => 'DSP Applications'])

@section('title', 'Authorised Delivery & Service Partners (DSP)')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-1">Authorised Delivery & Service Partners (DSP)</h4>
            <p class="text-muted small mb-0">Manage DSP franchise applications, territory allocations, security deposits, and approvals</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('dsp.apply') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-link-external me-1"></i> Public Apply Link
            </a>
            <a href="{{ route('admin.dsp.create') }}" class="btn btn-primary btn-sm fw-semibold px-3">
                <i class="bx bx-plus me-1"></i> Register New Partner
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3 py-2 px-3 small" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stat Cards -->
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card border border-light-subtle shadow-sm mb-0">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Total Applications</span>
                        <h4 class="fw-bold text-dark mb-0 mt-1">{{ $stats['total'] }}</h4>
                    </div>
                    <div class="avatar-sm rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fs-20">
                        <i class="bx bx-file"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border border-light-subtle shadow-sm mb-0">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Pending Review</span>
                        <h4 class="fw-bold text-info mb-0 mt-1">{{ $stats['pending'] }}</h4>
                    </div>
                    <div class="avatar-sm rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center fs-20">
                        <i class="bx bx-time"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border border-light-subtle shadow-sm mb-0">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Under Review</span>
                        <h4 class="fw-bold text-warning mb-0 mt-1">{{ $stats['under_review'] }}</h4>
                    </div>
                    <div class="avatar-sm rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center fs-20">
                        <i class="bx bx-search-alt"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border border-light-subtle shadow-sm mb-0">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small text-uppercase fw-semibold">Approved Partners</span>
                        <h4 class="fw-bold text-success mb-0 mt-1">{{ $stats['approved'] }}</h4>
                    </div>
                    <div class="avatar-sm rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center fs-20">
                        <i class="bx bx-check-shield"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="card border border-light-subtle shadow-sm mb-3">
        <div class="card-body p-2">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <!-- Status Pills -->
                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('admin.dsp.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}" 
                       class="btn {{ ($status ?? 'all') === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">
                        All <span class="badge bg-secondary ms-1">{{ $stats['total'] }}</span>
                    </a>
                    <a href="{{ route('admin.dsp.index', array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}" 
                       class="btn {{ ($status ?? '') === 'pending' ? 'btn-info text-dark' : 'btn-outline-secondary' }}">
                        Pending <span class="badge bg-secondary ms-1">{{ $stats['pending'] }}</span>
                    </a>
                    <a href="{{ route('admin.dsp.index', array_merge(request()->except('status', 'page'), ['status' => 'under_review'])) }}" 
                       class="btn {{ ($status ?? '') === 'under_review' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">
                        Under Review <span class="badge bg-secondary ms-1">{{ $stats['under_review'] }}</span>
                    </a>
                    <a href="{{ route('admin.dsp.index', array_merge(request()->except('status', 'page'), ['status' => 'approved'])) }}" 
                       class="btn {{ ($status ?? '') === 'approved' ? 'btn-success' : 'btn-outline-secondary' }}">
                        Approved <span class="badge bg-white text-success ms-1">{{ $stats['approved'] }}</span>
                    </a>
                    <a href="{{ route('admin.dsp.index', array_merge(request()->except('status', 'page'), ['status' => 'rejected'])) }}" 
                       class="btn {{ ($status ?? '') === 'rejected' ? 'btn-danger' : 'btn-outline-secondary' }}">
                        Rejected <span class="badge bg-secondary ms-1">{{ $stats['rejected'] }}</span>
                    </a>
                </div>

                <!-- Search -->
                <form action="{{ route('admin.dsp.index') }}" method="GET" class="d-flex align-items-center gap-2 m-0">
                    @if(!empty($status) && $status !== 'all')
                        <input type="hidden" name="status" value="{{ $status }}">
                    @endif
                    <div class="input-group input-group-sm" style="max-width: 280px;">
                        <input type="text" name="q" value="{{ $search ?? '' }}" class="form-control" placeholder="Search applicant, firm, mobile...">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bx bx-search"></i>
                        </button>
                    </div>

                    @if(!empty($search))
                        <a href="{{ route('admin.dsp.index', ['status' => $status ?? 'all']) }}" class="btn btn-sm btn-light text-muted" title="Clear search">
                            <i class="bx bx-x"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border border-light-subtle shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Application No.</th>
                            <th>Applicant & Firm</th>
                            <th>Territory / Location</th>
                            <th>Premises & Fleet</th>
                            <th>Security Deposit</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $app)
                            <tr>
                                <td class="ps-3">
                                    <a href="{{ route('admin.dsp.show', $app->id) }}" class="fw-bold text-primary font-monospace fs-13">
                                        {{ $app->application_number }}
                                    </a>
                                    <div class="text-muted micro">
                                        {{ $app->application_date ? $app->application_date->format('d M, Y') : $app->created_at->format('d M, Y') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $app->applicant_name }}</div>
                                    <div class="text-muted small">
                                        <i class="bx bx-briefcase me-1"></i>{{ $app->business_name }}
                                        <span class="badge bg-light text-secondary border ms-1 micro">{{ ucfirst(str_replace('_', ' ', $app->business_constitution)) }}</span>
                                    </div>
                                    <div class="text-muted micro">
                                        <i class="bx bx-phone me-1"></i>{{ $app->mobile }}
                                        @if($app->email) | {{ $app->email }} @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="text-dark small fw-semibold">{{ $app->preferred_territory_area ?: 'Not Specified' }}</div>
                                    <div class="text-muted micro">
                                        {{ $app->district ? $app->district . ', ' : '' }}{{ $app->state ?: '' }}
                                        @if($app->pincodes)
                                            <span class="badge bg-light text-dark font-monospace micro">PIN: {{ $app->pincodes }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="small">
                                        <span class="fw-semibold text-dark">{{ $app->total_area_sqft ? number_format($app->total_area_sqft) . ' sq.ft' : 'N/A' }}</span>
                                        <span class="text-muted micro">({{ ucfirst($app->premises_type) }})</span>
                                    </div>
                                    <div class="text-muted micro mt-1">
                                        <i class="bx bx-wrench me-1"></i>{{ $app->technicians_count }} Techs |
                                        <i class="bx bx-car me-1"></i>{{ $app->total_vehicles }} Vehicles
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-13">₹{{ number_format($app->security_deposit_amount, 2) }}</div>
                                    @if($app->deposit_payment_status === 'verified')
                                        <span class="badge bg-success-subtle text-success micro">Verified</span>
                                    @elseif($app->deposit_payment_status === 'paid')
                                        <span class="badge bg-warning-subtle text-warning micro">Payment Submitted</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-muted micro">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $app->status_badge['class'] }} fs-12">
                                        {{ $app->status_badge['text'] }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('admin.dsp.show', $app->id) }}" class="btn btn-sm btn-outline-primary" title="View & Review Application">
                                            <i class="bx bx-show me-1"></i> Review
                                        </a>
                                        <form action="{{ route('admin.dsp.destroy', $app->id) }}" method="POST" onsubmit="return confirm('Delete this application?');" class="d-inline">
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
                                    <i class="bx bx-file-blank fs-36 d-block mb-2 text-muted"></i>
                                    <h6>No DSP applications found</h6>
                                    <p class="small text-muted mb-3">Prospective delivery and service partners can apply online or you can register them manually.</p>
                                    <a href="{{ route('admin.dsp.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bx bx-plus me-1"></i> Register First Partner
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($applications->hasPages())
                <div class="card-footer bg-white border-top py-3 d-flex justify-content-end">
                    {{ $applications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
