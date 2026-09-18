@extends('adminlayouts.vertical', ['title' => 'Register DSP Partner'])

@section('title', 'Register Authorised Delivery & Service Partner (DSP)')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Register Authorised Delivery & Service Partner (DSP)</h4>
            <p class="text-muted small mb-0">Directly enter partner application details across all 8 form sections</p>
        </div>
        <a href="{{ route('admin.dsp.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back to List
        </a>
    </div>

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border border-light-subtle shadow-sm">
        <div class="card-header bg-white py-3 border-bottom">
            <h6 class="fw-bold text-dark mb-0">DSP Application Form Details</h6>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.dsp.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- TOP / HEADER INFO -->
                <div class="p-3 bg-light rounded border border-light-subtle mb-4">
                    <h6 class="fw-bold text-dark mb-3">Territory & Application Header</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-dark">Application Date</label>
                            <input type="date" name="application_date" class="form-control" value="{{ old('application_date', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-dark">Preferred Territory / Area</label>
                            <input type="text" name="preferred_territory_area" class="form-control" placeholder="e.g. Nashik City, Pune East" value="{{ old('preferred_territory_area') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-dark">PIN Code(s)</label>
                            <input type="text" name="pincodes" class="form-control" placeholder="422001, 422002" value="{{ old('pincodes') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-dark">District</label>
                            <input type="text" name="district" class="form-control" placeholder="Nashik" value="{{ old('district') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-dark">State</label>
                            <input type="text" name="state" class="form-control" placeholder="Maharashtra" value="{{ old('state') }}">
                        </div>
                    </div>
                </div>

                <!-- 1. APPLICANT DETAILS -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark text-uppercase border-bottom pb-2 mb-3">
                        <span class="badge bg-primary me-2">1</span> Applicant Details
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Applicant / Authorised Person Name *</label>
                            <input type="text" name="applicant_name" class="form-control" required placeholder="Full Name" value="{{ old('applicant_name') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Father's / Spouse Name</label>
                            <input type="text" name="father_or_spouse_name" class="form-control" value="{{ old('father_or_spouse_name') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Mobile Phone *</label>
                            <input type="text" name="mobile" class="form-control" required placeholder="10-digit mobile number" value="{{ old('mobile') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">WhatsApp Number</label>
                            <input type="text" name="whatsapp" class="form-control" placeholder="WhatsApp contact" value="{{ old('whatsapp') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="name@domain.com" value="{{ old('email') }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold text-dark">Residential Address</label>
                            <input type="text" name="residential_address" class="form-control" placeholder="Full residential street address" value="{{ old('residential_address') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Residential PIN Code</label>
                            <input type="text" name="residential_pincode" class="form-control" placeholder="6-digit PIN" value="{{ old('residential_pincode') }}">
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
                            <label class="form-label fw-semibold text-dark">Business / Firm Name *</label>
                            <input type="text" name="business_name" class="form-control" required placeholder="Registered Firm Name" value="{{ old('business_name') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Business Constitution *</label>
                            <select name="business_constitution" class="form-select">
                                <option value="proprietorship" {{ old('business_constitution') === 'proprietorship' ? 'selected' : '' }}>Proprietorship</option>
                                <option value="partnership" {{ old('business_constitution') === 'partnership' ? 'selected' : '' }}>Partnership</option>
                                <option value="llp" {{ old('business_constitution') === 'llp' ? 'selected' : '' }}>LLP</option>
                                <option value="private_limited" {{ old('business_constitution') === 'private_limited' ? 'selected' : '' }}>Private Limited Company</option>
                                <option value="other" {{ old('business_constitution') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Year Established</label>
                            <input type="text" name="year_established" class="form-control" placeholder="e.g. 2018" value="{{ old('year_established') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-dark">PAN</label>
                            <input type="text" name="pan" class="form-control text-uppercase" placeholder="ABCDE1234F" value="{{ old('pan') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-dark">GSTIN (if applicable)</label>
                            <input type="text" name="gstin" class="form-control text-uppercase" placeholder="27ABCDE1234F1Z5" value="{{ old('gstin') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Existing Business Activity</label>
                            <input type="text" name="existing_business_activity" class="form-control" placeholder="e.g. Automobile Spare Parts, Electronics Sales" value="{{ old('existing_business_activity') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-dark">Years of Experience</label>
                            <input type="text" name="years_of_experience" class="form-control" placeholder="e.g. 5" value="{{ old('years_of_experience') }}">
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
                            <label class="form-label fw-semibold text-dark">Premises</label>
                            <select name="premises_type" class="form-select">
                                <option value="owned" {{ old('premises_type') === 'owned' ? 'selected' : '' }}>Owned</option>
                                <option value="rented" {{ old('premises_type') === 'rented' ? 'selected' : '' }}>Rented</option>
                                <option value="leased" {{ old('premises_type') === 'leased' ? 'selected' : '' }}>Leased</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Complete Address</label>
                            <input type="text" name="complete_address" class="form-control" placeholder="Premises address" value="{{ old('complete_address') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-dark">Premises PIN Code</label>
                            <input type="text" name="premises_pincode" class="form-control" value="{{ old('premises_pincode') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-dark">Total Area (Sq. Ft.)</label>
                            <input type="number" step="0.01" name="total_area_sqft" class="form-control" placeholder="e.g. 1500" value="{{ old('total_area_sqft') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-dark">Frontage (Feet)</label>
                            <input type="number" step="0.01" name="frontage_feet" class="form-control" placeholder="e.g. 25" value="{{ old('frontage_feet') }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-dark d-block">Available Facilities</label>
                            <div class="d-flex flex-wrap gap-3">
                                @php
                                    $facilities = [
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
                                @endphp
                                @foreach($facilities as $key => $label)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="available_facilities[]" value="{{ $key }}" id="fac_{{ $key }}" checked>
                                        <label class="form-check-label text-dark small" for="fac_{{ $key }}">{{ $label }}</label>
                                    </div>
                                @endforeach
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
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark d-block">Presently operate a service centre/workshop?</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="presently_operate_service_centre" id="sc_yes" value="1" checked>
                                <label class="form-check-label small" for="sc_yes">YES</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="presently_operate_service_centre" id="sc_no" value="0">
                                <label class="form-check-label small" for="sc_no">NO</label>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-dark">No. of Technicians</label>
                            <input type="number" name="technicians_count" class="form-control" value="{{ old('technicians_count', 2) }}" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-dark">EV Technician</label>
                            <select name="ev_technician_status" class="form-select">
                                <option value="yes">YES</option>
                                <option value="no">NO</option>
                                <option value="will_recruit">Will Recruit</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-dark">Electrical/Electronics</label>
                            <select name="electrical_technician_status" class="form-select">
                                <option value="yes">YES</option>
                                <option value="no">NO</option>
                                <option value="will_recruit">Will Recruit</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-dark">Home Appliance Tech</label>
                            <select name="home_appliance_technician_status" class="form-select">
                                <option value="yes">YES</option>
                                <option value="no">NO</option>
                                <option value="will_recruit">Will Recruit</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="agree_to_nexvia_training" value="1" id="agreeTraining" checked>
                                <label class="form-check-label text-dark fw-semibold small" for="agreeTraining">
                                    Agree to mandatory NEXVIA™ technical training & certification standards
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. PRODUCTS & DELIVERY CAPABILITY -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark text-uppercase border-bottom pb-2 mb-3">
                        <span class="badge bg-primary me-2">5</span> Products & Delivery Capability
                    </h6>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark d-block">Products Capable of Handling</label>
                        <div class="row g-2">
                            @php
                                $products = [
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
                            @endphp
                            @foreach($products as $pKey => $pLabel)
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="products_handled[]" value="{{ $pKey }}" id="prod_{{ $pKey }}" checked>
                                        <label class="form-check-label text-dark small" for="prod_{{ $pKey }}">{{ $pLabel }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-dark">Two-Wheeler Vehicles</label>
                            <input type="number" name="vehicles_two_wheeler" class="form-control" value="2" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-dark">Three-Wheeler Vehicles</label>
                            <input type="number" name="vehicles_three_wheeler" class="form-control" value="1" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold text-dark">Pickup / LCV</label>
                            <input type="number" name="vehicles_pickup_lcv" class="form-control" value="1" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-dark">Other Transport</label>
                            <input type="text" name="vehicles_other" class="form-control" placeholder="e.g. 1 Van">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-dark">Max Delivery Radius (KM)</label>
                            <input type="number" step="0.1" name="max_delivery_radius_km" class="form-control" value="30">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="pdi_handover_sop" value="1" id="pdiCheck" checked>
                                <label class="form-check-label text-dark small" for="pdiCheck">Pre-Delivery Inspection (PDI) & Handover SOP Compliant</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="otp_delivery_confirmation" value="1" id="otpCheck" checked>
                                <label class="form-check-label text-dark small" for="otpCheck">Mandatory OTP Delivery Confirmation</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. SECURITY STOCK DEPOSIT -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark text-uppercase border-bottom pb-2 mb-3">
                        <span class="badge bg-primary me-2">6</span> Security Stock Deposit
                    </h6>
                    <div class="card bg-light border-0 p-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h4 class="fw-bold text-dark mb-0">₹ 10,00,000</h4>
                                <span class="text-muted micro">Security Stock Deposit (Against Company Inventory)</span>
                            </div>
                            <div class="text-muted micro">
                                Central Sales • Company Inventory • Local Delivery • Local Service
                            </div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Deposit Payment Status</label>
                            <select name="deposit_payment_status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="paid">Paid</option>
                                <option value="verified">Verified</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Bank UTR / Transaction Reference</label>
                            <input type="text" name="deposit_transaction_reference" class="form-control" placeholder="e.g. UTR123456789">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Deposit Payment Proof / Receipt</label>
                            <input type="file" name="deposit_payment_proof" class="form-control" accept="image/*,.pdf">
                        </div>
                    </div>
                </div>

                <!-- 7 & 8. STATUS & NOTES -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark text-uppercase border-bottom pb-2 mb-3">
                        <span class="badge bg-primary me-2">7</span> Initial Status & Admin Notes
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Initial Status *</label>
                            <select name="status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="under_review">Under Review</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Upload Signature / Document</label>
                            <input type="file" name="signature_file" class="form-control" accept="image/*,.pdf">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Signatory Name</label>
                            <input type="text" name="declaration_signature_name" class="form-control" placeholder="Signatory full name">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold text-dark">Admin Remarks / Verification Notes</label>
                            <textarea name="admin_notes" class="form-control" rows="2" placeholder="Territory notes, background check findings..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        <i class="bx bx-save me-1"></i> Register DSP Partner
                    </button>
                    <a href="{{ route('admin.dsp.index') }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
