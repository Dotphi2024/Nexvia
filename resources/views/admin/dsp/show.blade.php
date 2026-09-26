@extends('adminlayouts.vertical', ['title' => 'DSP Application – ' . $application->application_number])

@section('title', 'DSP Application – ' . $application->application_number)

@section('content')
<div class="container-fluid py-3">
    <!-- Action / Print Header Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.dsp.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
            <div>
                <h4 class="fw-bold text-dark mb-0 d-inline-block">Application: {{ $application->application_number }}</h4>
                <span class="badge {{ $application->status_badge['class'] }} ms-2 fs-12">
                    {{ $application->status_badge['text'] }}
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#passwordModal">
                <i class="bx bx-key me-1"></i> Set Portal Password
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bx bx-printer me-1"></i> Print / PDF
            </button>
            <button type="button" class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                <i class="bx bx-check-shield me-1"></i> Update Status
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-3 py-2 px-3 small" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- DSP Partner Portal Status & Wallet Bar -->
    <div class="card border border-light-subtle shadow-sm mb-3">
        <div class="card-body p-3">
            <div class="row align-items-center g-3">
                <div class="col-md-3 border-end">
                    <span class="micro text-muted text-uppercase fw-bold d-block">Login Mobile</span>
                    <strong class="text-dark font-monospace">{{ $application->mobile }}</strong>
                    <span class="badge bg-light text-dark ms-1 micro">Portal Login ID</span>
                </div>
                <div class="col-md-3 border-end">
                    <span class="micro text-muted text-uppercase fw-bold d-block">Cash Wallet Balance</span>
                    <strong class="text-success fs-5">₹{{ number_format($application->wallet_balance ?? 0, 2) }}</strong>
                </div>
                <div class="col-md-3 border-end">
                    <span class="micro text-muted text-uppercase fw-bold d-block">5% Commission Earned</span>
                    <strong class="text-dark">₹{{ number_format($application->total_earned ?? 0, 2) }}</strong>
                    <span class="micro text-muted d-block">Redeemed: ₹{{ number_format($application->total_redeemed ?? 0, 2) }}</span>
                </div>
                <div class="col-md-3 text-end">
                    <button type="button" class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#passwordModal">
                        <i class="bx bx-lock-alt me-1"></i> Change Portal Password
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Set Password Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-white border-bottom py-3">
                    <h6 class="modal-title fw-bold text-dark">
                        <i class="bx bx-key text-primary me-1"></i> Set Portal Password for {{ $application->applicant_name }}
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.dsp.update.password', $application->id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-3">
                        <p class="small text-muted mb-3">
                            The partner can log into the dedicated DSP Portal at <code>/dsp/login</code> using their mobile number (<strong>{{ $application->mobile }}</strong>) and this password.
                        </p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Partner Email (for Credential Dispatch)</label>
                            <input type="email" name="email" class="form-control" value="{{ $application->email }}" placeholder="e.g. partner@example.com">
                            <span class="micro text-muted">Login credentials will be automatically emailed from <code>{{ config('mail.from.address', 'nexviadls@gmail.com') }}</code>.</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">New Password *</label>
                            <input type="text" name="password" class="form-control" placeholder="Enter minimum 6 character password" required minlength="6">
                            <span class="micro text-muted">e.g. dsp@123 or a secure custom password</span>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-semibold">Save & Email Credentials</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Application Document Container -->
    <div class="card border border-light-subtle shadow-sm mb-4 print-card">
        <!-- Official Document Header -->
        <div class="card-header bg-dark text-white p-4 rounded-top">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h3 class="fw-bold text-uppercase mb-0 text-white" style="letter-spacing: 1.5px;">NEXVIA™</h3>
                    <p class="micro text-light-50 mb-0">Better Living • A Cleaner Tomorrow</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary fs-12 px-3 py-1 text-uppercase">Authorised Delivery & Service Partner (DSP)</span>
                    <h6 class="text-light mt-1 mb-0 micro">APPLICATION FORM</h6>
                </div>
            </div>
            <div class="mt-3 pt-2 border-top border-secondary text-center small text-light-50 font-monospace">
                CENTRAL SALES &nbsp;•&nbsp; COMPANY INVENTORY &nbsp;•&nbsp; LOCAL DELIVERY &nbsp;•&nbsp; LOCAL SERVICE
            </div>
        </div>

        <div class="card-body p-4">
            <!-- Application Metadata Row -->
            <div class="row g-3 p-3 bg-light rounded border border-light-subtle mb-4">
                <div class="col-md-3">
                    <span class="text-muted micro d-block text-uppercase fw-semibold">Application No.</span>
                    <strong class="text-dark font-monospace fs-14">{{ $application->application_number }}</strong>
                </div>
                <div class="col-md-3">
                    <span class="text-muted micro d-block text-uppercase fw-semibold">Application Date</span>
                    <span class="text-dark">{{ $application->application_date ? $application->application_date->format('d/m/Y') : $application->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted micro d-block text-uppercase fw-semibold">Preferred Territory / Area</span>
                    <span class="text-dark fw-semibold">{{ $application->preferred_territory_area ?: '—' }}</span>
                </div>
                <div class="col-md-3">
                    <span class="text-muted micro d-block text-uppercase fw-semibold">District & State</span>
                    <span class="text-dark">{{ $application->district ?: '—' }}, {{ $application->state ?: '—' }}</span>
                    @if($application->pincodes)
                        <span class="badge bg-secondary-subtle text-dark micro ms-1">{{ $application->pincodes }}</span>
                    @endif
                </div>
            </div>

            <!-- 1. APPLICANT DETAILS -->
            <div class="mb-4">
                <h6 class="fw-bold text-dark text-uppercase border-bottom pb-2 mb-3">
                    <span class="badge bg-primary me-2">1</span> Applicant Details
                </h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Applicant / Authorised Person Name</span>
                        <strong class="text-dark">{{ $application->applicant_name }}</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Father's / Spouse Name</span>
                        <span class="text-dark">{{ $application->father_or_spouse_name ?: '—' }}</span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Date of Birth</span>
                        <span class="text-dark">{{ $application->date_of_birth ? $application->date_of_birth->format('d/m/Y') : '—' }}</span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Mobile Phone</span>
                        <span class="text-dark fw-semibold"><i class="bx bx-phone me-1"></i>{{ $application->mobile }}</span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">WhatsApp Number</span>
                        <span class="text-dark"><i class="bx bxl-whatsapp text-success me-1"></i>{{ $application->whatsapp ?: '—' }}</span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Email Address</span>
                        <span class="text-dark"><i class="bx bx-envelope me-1"></i>{{ $application->email ?: '—' }}</span>
                    </div>
                    <div class="col-md-8">
                        <span class="text-muted micro d-block">Residential Address</span>
                        <span class="text-dark">{{ $application->residential_address ?: '—' }}</span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">PIN Code</span>
                        <span class="text-dark font-monospace">{{ $application->residential_pincode ?: '—' }}</span>
                    </div>
                </div>
            </div>

            <!-- 2. BUSINESS DETAILS -->
            <div class="mb-4">
                <h6 class="fw-bold text-dark text-uppercase border-bottom pb-2 mb-3">
                    <span class="badge bg-primary me-2">2</span> Business Details
                </h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Business / Firm Name</span>
                        <strong class="text-dark fs-14">{{ $application->business_name }}</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Business Constitution</span>
                        <span class="badge bg-info-subtle text-info border border-info-subtle text-capitalize">
                            {{ str_replace('_', ' ', $application->business_constitution) }}
                            @if($application->business_constitution_other) ({{ $application->business_constitution_other }}) @endif
                        </span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Year Established</span>
                        <span class="text-dark font-monospace">{{ $application->year_established ?: '—' }}</span>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted micro d-block">PAN</span>
                        <span class="text-dark font-monospace fw-bold">{{ $application->pan ?: '—' }}</span>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted micro d-block">GSTIN</span>
                        <span class="text-dark font-monospace fw-bold">{{ $application->gstin ?: '—' }}</span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Existing Business Activity</span>
                        <span class="text-dark">{{ $application->existing_business_activity ?: '—' }}</span>
                    </div>
                    <div class="col-md-2">
                        <span class="text-muted micro d-block">Experience</span>
                        <span class="text-dark">{{ $application->years_of_experience ? $application->years_of_experience . ' Yrs' : '—' }}</span>
                    </div>
                </div>
            </div>

            <!-- 3. PROPOSED DSP LOCATION -->
            <div class="mb-4">
                <h6 class="fw-bold text-dark text-uppercase border-bottom pb-2 mb-3">
                    <span class="badge bg-primary me-2">3</span> Proposed DSP Location
                </h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <span class="text-muted micro d-block">Premises Type</span>
                        <span class="badge bg-secondary-subtle text-dark text-capitalize">{{ $application->premises_type }}</span>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted micro d-block">Complete Premises Address</span>
                        <span class="text-dark">{{ $application->complete_address ?: '—' }}</span>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted micro d-block">Premises PIN Code</span>
                        <span class="text-dark font-monospace">{{ $application->premises_pincode ?: '—' }}</span>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted micro d-block">Total Area</span>
                        <span class="text-dark fw-bold">{{ $application->total_area_sqft ? number_format($application->total_area_sqft) . ' Sq. Ft.' : '—' }}</span>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted micro d-block">Frontage</span>
                        <span class="text-dark fw-bold">{{ $application->frontage_feet ? $application->frontage_feet . ' Feet' : '—' }}</span>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted micro d-block mb-1">Available Facilities</span>
                        <div class="d-flex flex-wrap gap-1">
                            @php
                                $facilitiesMap = [
                                    'product_storage'   => 'Product Storage',
                                    'cctv'              => 'CCTV',
                                    'customer_reception'=> 'Customer Reception',
                                    'fire_safety'       => 'Fire Safety',
                                    'service_workshop'  => 'Service / Workshop',
                                    'secure_storage'    => 'Secure Storage',
                                    'electricity'       => 'Electricity',
                                    'internet'          => 'Internet',
                                    'parking'           => 'Parking',
                                ];
                                $userFacilities = is_array($application->available_facilities) ? $application->available_facilities : [];
                            @endphp
                            @forelse($userFacilities as $facKey)
                                <span class="badge bg-success-subtle text-success border border-success-subtle micro">
                                    <i class="bx bx-check me-1"></i>{{ $facilitiesMap[$facKey] ?? ucfirst(str_replace('_', ' ', $facKey)) }}
                                </span>
                            @empty
                                <span class="text-muted small">No specific facilities indicated</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. SERVICE CAPABILITY -->
            <div class="mb-4">
                <h6 class="fw-bold text-dark text-uppercase border-bottom pb-2 mb-3">
                    <span class="badge bg-primary me-2">4</span> Service Capability
                </h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <span class="text-muted micro d-block">Operate Workshop / Service Centre?</span>
                        <span class="badge {{ $application->presently_operate_service_centre ? 'bg-success' : 'bg-secondary' }}">
                            {{ $application->presently_operate_service_centre ? 'YES' : 'NO' }}
                        </span>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted micro d-block">Total Technicians</span>
                        <span class="text-dark fw-bold fs-14">{{ $application->technicians_count }}</span>
                    </div>
                    <div class="col-md-2">
                        <span class="text-muted micro d-block">EV Technician</span>
                        <span class="badge bg-light text-dark border text-uppercase">{{ str_replace('_', ' ', $application->ev_technician_status) }}</span>
                    </div>
                    <div class="col-md-2">
                        <span class="text-muted micro d-block">Electrical / Electronics</span>
                        <span class="badge bg-light text-dark border text-uppercase">{{ str_replace('_', ' ', $application->electrical_technician_status) }}</span>
                    </div>
                    <div class="col-md-2">
                        <span class="text-muted micro d-block">Home Appliances</span>
                        <span class="badge bg-light text-dark border text-uppercase">{{ str_replace('_', ' ', $application->home_appliance_technician_status) }}</span>
                    </div>
                    <div class="col-md-12">
                        <span class="text-muted micro d-block">Agreement to NEXVIA™ Training:</span>
                        <span class="badge {{ $application->agree_to_nexvia_training ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} fs-12">
                            <i class="bx {{ $application->agree_to_nexvia_training ? 'bx-check' : 'bx-x' }} me-1"></i>
                            {{ $application->agree_to_nexvia_training ? 'Agreed to mandatory technical training' : 'Did not agree' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 5. PRODUCTS & DELIVERY CAPABILITY -->
            <div class="mb-4">
                <h6 class="fw-bold text-dark text-uppercase border-bottom pb-2 mb-3">
                    <span class="badge bg-primary me-2">5</span> Products & Delivery Capability
                </h6>
                <div class="row g-3">
                    <div class="col-md-7">
                        <span class="text-muted micro d-block mb-1">Products Capable of Handling</span>
                        <div class="d-flex flex-wrap gap-1">
                            @php
                                $productsMap = [
                                    'electric_scooters' => 'Electric Scooters',
                                    'smart_tvs'         => 'Smart TVs',
                                    'refrigerators'     => 'Refrigerators',
                                    'washing_machines'  => 'Washing Machines',
                                    'air_conditioners'  => 'Air Conditioners',
                                    'mixer_grinders'    => 'Mixer Grinders',
                                    'gas_stoves'        => 'Gas Stoves',
                                    'kitchen_chimneys'  => 'Kitchen Chimneys',
                                    'ovens'             => 'Ovens',
                                    'electric_irons'    => 'Electric Irons',
                                    'speakers_audio'    => 'Speakers / Audio',
                                    'all_approved'      => 'All Approved Products',
                                ];
                                $userProducts = is_array($application->products_handled) ? $application->products_handled : [];
                            @endphp
                            @forelse($userProducts as $prodKey)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle micro">
                                    {{ $productsMap[$prodKey] ?? ucfirst(str_replace('_', ' ', $prodKey)) }}
                                </span>
                            @empty
                                <span class="text-muted small">No specific products selected</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-md-5">
                        <span class="text-muted micro d-block mb-1">Fleet Vehicles</span>
                        <div class="d-flex gap-2">
                            <span class="badge bg-light text-dark border p-2 text-center">
                                <span class="d-block micro text-muted">2-Wheeler</span>
                                <strong class="fs-14">{{ $application->vehicles_two_wheeler }}</strong>
                            </span>
                            <span class="badge bg-light text-dark border p-2 text-center">
                                <span class="d-block micro text-muted">3-Wheeler</span>
                                <strong class="fs-14">{{ $application->vehicles_three_wheeler }}</strong>
                            </span>
                            <span class="badge bg-light text-dark border p-2 text-center">
                                <span class="d-block micro text-muted">Pickup/LCV</span>
                                <strong class="fs-14">{{ $application->vehicles_pickup_lcv }}</strong>
                            </span>
                            @if($application->vehicles_other)
                                <span class="badge bg-light text-dark border p-2 text-center">
                                    <span class="d-block micro text-muted">Other</span>
                                    <strong class="fs-14">{{ $application->vehicles_other }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Max Delivery Radius</span>
                        <strong class="text-dark">{{ $application->max_delivery_radius_km ? $application->max_delivery_radius_km . ' KM' : '—' }}</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">PDI / Handover SOP</span>
                        <span class="badge {{ $application->pdi_handover_sop ? 'bg-success' : 'bg-secondary' }}">
                            {{ $application->pdi_handover_sop ? 'YES - Compliant' : 'NO' }}
                        </span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">OTP Delivery Confirmation</span>
                        <span class="badge {{ $application->otp_delivery_confirmation ? 'bg-success' : 'bg-secondary' }}">
                            {{ $application->otp_delivery_confirmation ? 'YES - Enabled' : 'NO' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 6. SECURITY STOCK DEPOSIT -->
            <div class="mb-4">
                <h6 class="fw-bold text-dark text-uppercase border-bottom pb-2 mb-3">
                    <span class="badge bg-primary me-2">6</span> Security Stock Deposit
                </h6>
                <div class="card bg-light border-0 p-3 mb-3">
                    <div class="row align-items-center g-3">
                        <div class="col-md-4 text-center border-end">
                            <span class="text-muted micro d-block text-uppercase fw-semibold">Security Deposit Required</span>
                            <h3 class="fw-bold text-dark mb-0">₹ 10,00,000</h3>
                            <small class="text-muted micro">(Against Company Inventory)</small>
                        </div>
                        <div class="col-md-8">
                            <ul class="list-unstyled mb-0 micro text-muted ps-2">
                                <li><i class="bx bx-check text-success me-1"></i> Security stock deposit and not a purchase of products.</li>
                                <li><i class="bx bx-check text-success me-1"></i> Company inventory remains property of NEXVIA™ / applicable entity.</li>
                                <li><i class="bx bx-check text-success me-1"></i> Partner maintains safe custody and proper records.</li>
                                <li><i class="bx bx-check text-success me-1"></i> Reports loss/damage immediately and permits stock audits.</li>
                                <li><i class="bx bx-check text-success me-1"></i> Governed by the executed DSP Agreement.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Deposit Payment Status</span>
                        @if($application->deposit_payment_status === 'verified')
                            <span class="badge bg-success fs-13">Verified</span>
                        @elseif($application->deposit_payment_status === 'paid')
                            <span class="badge bg-warning text-dark fs-13">Paid / Verification Pending</span>
                        @else
                            <span class="badge bg-secondary fs-13">Pending Payment</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Payment UTR / Reference</span>
                        <span class="text-dark font-monospace fw-bold">{{ $application->deposit_transaction_reference ?: '—' }}</span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Payment Receipt / Proof</span>
                        @if($application->deposit_payment_proof)
                            <a href="{{ asset($application->deposit_payment_proof) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-download me-1"></i> View Receipt
                            </a>
                        @else
                            <span class="text-muted small">No receipt uploaded</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- 7. COMPANY BANK DETAILS -->
            <div class="mb-4">
                <h6 class="fw-bold text-dark text-uppercase border-bottom pb-2 mb-3">
                    <span class="badge bg-primary me-2">7</span> Company Bank Details (For Deposit Remittance)
                </h6>
                <div class="row g-3 p-3 bg-light rounded border border-light-subtle">
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Bank</span>
                        <strong class="text-dark">IDFC FIRST BANK LIMITED</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Account Name</span>
                        <strong class="text-dark">DLS AGRO INFRAVENTURE PVT LTD</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Account Number</span>
                        <strong class="text-dark font-monospace fs-14">10048728156</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">IFSC Code</span>
                        <strong class="text-dark font-monospace">IDFB0042281</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Branch</span>
                        <strong class="text-dark">Nashik</strong>
                    </div>
                </div>

                <div class="row align-items-center g-3 p-3 bg-white rounded border border-light-subtle mt-2">
                    <div class="col-md-3 text-center">
                        <img src="{{ asset('images/dls_payment_qr.png') }}" alt="Official QR Code" class="img-fluid rounded border shadow-sm" style="max-height: 180px;">
                        <span class="d-block micro text-muted mt-1">Official IDFC First Bank QR</span>
                    </div>
                    <div class="col-md-9">
                        <h6 class="fw-bold text-dark mb-1">
                            <i class="bx bx-qr-scan text-primary me-1"></i> Authorised Payment QR Code
                        </h6>
                        <p class="small text-muted mb-2">
                            Official company QR code for verifying partner deposit transfers across all UPI applications.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <div class="p-2 bg-light rounded border font-monospace small">
                                <span class="text-muted">UPI ID:</span> <strong class="text-danger">dlsagroin.09@idfcbank</strong>
                            </div>
                            <div class="p-2 bg-light rounded border font-monospace small">
                                <span class="text-muted">Account:</span> <strong class="text-dark">DLS AGRO INFRAVENTURE PRIVATE LIMITED</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 8. APPLICANT DECLARATION -->
            <div class="mb-3">
                <h6 class="fw-bold text-dark text-uppercase border-bottom pb-2 mb-3">
                    <span class="badge bg-primary me-2">8</span> Applicant Declaration
                </h6>
                <p class="text-muted small fst-italic">
                    "I/We declare that the information provided is true and correct. Submission of this application does not create a right to appointment. Final appointment is subject to verification, approval, execution of the DSP Agreement and fulfilment of company conditions."
                </p>
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Signatory Name</span>
                        <strong class="text-dark">{{ $application->declaration_signature_name ?: $application->applicant_name }}</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Declaration Date</span>
                        <span class="text-dark">{{ $application->declaration_date ? $application->declaration_date->format('d/m/Y') : $application->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted micro d-block">Signature File</span>
                        @if($application->signature_file)
                            <a href="{{ asset($application->signature_file) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="bx bx-show me-1"></i> View Signature
                            </a>
                        @else
                            <span class="text-muted small">Digitally Agreed</span>
                        @endif
                    </div>
                </div>
            </div>

            @if($application->admin_notes || $application->reviewed_at)
                <!-- Reviewer Notes & Audit -->
                <div class="mt-4 p-3 bg-warning-subtle rounded border border-warning-subtle">
                    <h6 class="fw-bold text-dark mb-1">
                        <i class="bx bx-note me-1 text-warning"></i> Admin Review Remarks
                    </h6>
                    <p class="text-dark small mb-1">{{ $application->admin_notes ?: 'No review notes entered yet.' }}</p>
                    <div class="micro text-muted mt-2">
                        Reviewed on {{ $application->reviewed_at ? $application->reviewed_at->format('d M, Y h:i A') : '—' }}
                        @if($application->reviewer)
                            by <strong>{{ $application->reviewer->name }}</strong>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-white border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark" id="updateStatusModalLabel">
                    <i class="bx bx-check-shield text-primary me-1"></i> Review & Update DSP Status
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.dsp.update.status', $application->id) }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Application Status *</label>
                        <select name="status" class="form-select" required>
                            <option value="pending" {{ $application->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="under_review" {{ $application->status === 'under_review' ? 'selected' : '' }}>Under Review</option>
                            <option value="approved" {{ $application->status === 'approved' ? 'selected' : '' }}>Approved (Franchise Granted)</option>
                            <option value="rejected" {{ $application->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Security Deposit Payment Status</label>
                        <select name="deposit_payment_status" class="form-select">
                            <option value="pending" {{ $application->deposit_payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $application->deposit_payment_status === 'paid' ? 'selected' : '' }}>Paid / Under Audit</option>
                            <option value="verified" {{ $application->deposit_payment_status === 'verified' ? 'selected' : '' }}>Verified in Bank Account</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Partner Email (for Notification & Login Details)</label>
                        <input type="email" name="email" class="form-control" value="{{ $application->email }}" placeholder="Enter partner email (e.g. partner@example.com)">
                        <span class="micro text-muted">Upon approval, login credentials and instructions are emailed from <code>{{ config('mail.from.address', 'nexviadls@gmail.com') }}</code> to this address.</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Portal Password <span class="fw-normal text-muted">(Optional for Approval)</span></label>
                        <input type="text" name="password" class="form-control" placeholder="Leave empty to auto-generate secure password">
                        <span class="micro text-muted">If left blank and approved, the system generates a secure temporary password and emails it immediately.</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Admin Remarks / Notes</label>
                        <textarea name="admin_notes" class="form-control" rows="3" 
                                  placeholder="Enter verification notes, territory allotment conditions, or reason for status...">{{ $application->admin_notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold px-3">Save Status & Send Credentials</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@media print {
    .app-sidebar, .topbar, .btn, .alert, .modal {
        display: none !important;
    }
    .print-card {
        border: none !important;
        box-shadow: none !important;
    }
    body {
        background: white !important;
    }
}
</style>
@endsection
