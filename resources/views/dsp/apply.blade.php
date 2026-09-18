<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Authorised Delivery & Service Partner (DSP) | NEXVIA™</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6fa;
            color: #2b3040;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .form-header {
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }
        .card {
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border-radius: 12px;
        }
        .section-badge {
            background: #4f46e5;
            color: white;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: bold;
            margin-right: 8px;
        }
        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
        }
        .bank-card {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-left: 4px solid #4f46e5;
        }
    </style>
</head>
<body class="py-4">
    <div class="container" style="max-width: 960px;">
        <div class="card overflow-hidden">
            <!-- Header -->
            <div class="form-header p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h2 class="fw-bold mb-0" style="letter-spacing: 1px;">NEXVIA™</h2>
                        <p class="mb-0 text-white-50 small">Better Living • A Cleaner Tomorrow</p>
                    </div>
                    <div class="text-md-end">
                        <span class="badge bg-warning text-dark fs-13 px-3 py-1 text-uppercase fw-bold">Authorised Delivery & Service Partner</span>
                        <div class="small text-white-50 mt-1">APPLICATION FORM</div>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top border-light border-opacity-25 text-center small text-white-50">
                    CENTRAL SALES &nbsp;•&nbsp; COMPANY INVENTORY &nbsp;•&nbsp; LOCAL DELIVERY &nbsp;•&nbsp; LOCAL SERVICE
                </div>
            </div>

            <!-- Form Body -->
            <div class="card-body p-4 p-md-5">
                @if(isset($errors) && $errors->any())
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4">
                        <strong>Please resolve the following:</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('dsp.apply.post') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- HEADER / LOCATION META -->
                    <div class="p-3 bg-light rounded border mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Preferred Territory / Area *</label>
                                <input type="text" name="preferred_territory_area" class="form-control" required placeholder="e.g. Nashik District" value="{{ old('preferred_territory_area') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">PIN Code(s) *</label>
                                <input type="text" name="pincodes" class="form-control" required placeholder="e.g. 422001, 422002" value="{{ old('pincodes') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">District *</label>
                                <input type="text" name="district" class="form-control" required placeholder="District" value="{{ old('district') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold small">State *</label>
                                <input type="text" name="state" class="form-control" required placeholder="State" value="{{ old('state') }}">
                            </div>
                        </div>
                    </div>

                    <!-- 1. APPLICANT DETAILS -->
                    <div class="mb-4">
                        <div class="section-title">
                            <span class="section-badge">1</span> Applicant Details
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Applicant / Authorised Person Name *</label>
                                <input type="text" name="applicant_name" class="form-control" required placeholder="Full Name" value="{{ old('applicant_name') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Father's / Spouse Name</label>
                                <input type="text" name="father_or_spouse_name" class="form-control" value="{{ old('father_or_spouse_name') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Mobile Phone *</label>
                                <input type="tel" name="mobile" class="form-control" required placeholder="10-digit mobile number" value="{{ old('mobile') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">WhatsApp Number</label>
                                <input type="tel" name="whatsapp" class="form-control" placeholder="WhatsApp number" value="{{ old('whatsapp') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Email Address *</label>
                                <input type="email" name="email" class="form-control" required placeholder="your.email@domain.com" value="{{ old('email') }}">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold small">Residential Address</label>
                                <input type="text" name="residential_address" class="form-control" placeholder="Complete address" value="{{ old('residential_address') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Residential PIN Code</label>
                                <input type="text" name="residential_pincode" class="form-control" placeholder="PIN Code" value="{{ old('residential_pincode') }}">
                            </div>
                        </div>
                    </div>

                    <!-- 2. BUSINESS DETAILS -->
                    <div class="mb-4">
                        <div class="section-title">
                            <span class="section-badge">2</span> Business Details
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Business / Firm Name *</label>
                                <input type="text" name="business_name" class="form-control" required placeholder="Firm Name" value="{{ old('business_name') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Business Constitution *</label>
                                <select name="business_constitution" class="form-select">
                                    <option value="proprietorship">Proprietorship</option>
                                    <option value="partnership">Partnership</option>
                                    <option value="llp">LLP</option>
                                    <option value="private_limited">Private Limited Company</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Year Established</label>
                                <input type="text" name="year_established" class="form-control" placeholder="e.g. 2019" value="{{ old('year_established') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">PAN *</label>
                                <input type="text" name="pan" class="form-control text-uppercase" placeholder="PAN Number" value="{{ old('pan') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">GSTIN (if applicable)</label>
                                <input type="text" name="gstin" class="form-control text-uppercase" placeholder="GSTIN" value="{{ old('gstin') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small">Existing Business Activity</label>
                                <input type="text" name="existing_business_activity" class="form-control" placeholder="Current line of business" value="{{ old('existing_business_activity') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold small">Experience (Yrs)</label>
                                <input type="text" name="years_of_experience" class="form-control" placeholder="Years" value="{{ old('years_of_experience') }}">
                            </div>
                        </div>
                    </div>

                    <!-- 3. PROPOSED DSP LOCATION -->
                    <div class="mb-4">
                        <div class="section-title">
                            <span class="section-badge">3</span> Proposed DSP Location
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Premises</label>
                                <select name="premises_type" class="form-select">
                                    <option value="owned">Owned</option>
                                    <option value="rented">Rented</option>
                                    <option value="leased">Leased</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Complete Premises Address</label>
                                <input type="text" name="complete_address" class="form-control" placeholder="Workshop / Showroom address" value="{{ old('complete_address') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Premises PIN Code</label>
                                <input type="text" name="premises_pincode" class="form-control" value="{{ old('premises_pincode') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Total Area (Sq. Ft.)</label>
                                <input type="number" step="0.01" name="total_area_sqft" class="form-control" placeholder="e.g. 1200" value="{{ old('total_area_sqft') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Frontage (Feet)</label>
                                <input type="number" step="0.01" name="frontage_feet" class="form-control" placeholder="e.g. 20" value="{{ old('frontage_feet') }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-semibold small d-block">Available Facilities</label>
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
                                    @foreach($facilities as $fKey => $fLabel)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="available_facilities[]" value="{{ $fKey }}" id="public_fac_{{ $fKey }}" checked>
                                            <label class="form-check-label small" for="public_fac_{{ $fKey }}">{{ $fLabel }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. SERVICE CAPABILITY -->
                    <div class="mb-4">
                        <div class="section-title">
                            <span class="section-badge">4</span> Service Capability
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small d-block">Presently operate a workshop?</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="presently_operate_service_centre" id="psc_yes" value="1" checked>
                                    <label class="form-check-label small" for="psc_yes">YES</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="presently_operate_service_centre" id="psc_no" value="0">
                                    <label class="form-check-label small" for="psc_no">NO</label>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold small">No. of Technicians</label>
                                <input type="number" name="technicians_count" class="form-control" value="2" min="0">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold small">EV Technician</label>
                                <select name="ev_technician_status" class="form-select">
                                    <option value="yes">YES</option>
                                    <option value="no">NO</option>
                                    <option value="will_recruit">Will Recruit</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold small">Electrical/Electronics</label>
                                <select name="electrical_technician_status" class="form-select">
                                    <option value="yes">YES</option>
                                    <option value="no">NO</option>
                                    <option value="will_recruit">Will Recruit</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold small">Home Appliances</label>
                                <select name="home_appliance_technician_status" class="form-select">
                                    <option value="yes">YES</option>
                                    <option value="no">NO</option>
                                    <option value="will_recruit">Will Recruit</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="agree_to_nexvia_training" value="1" id="agreeTrainingPublic" checked>
                                    <label class="form-check-label small" for="agreeTrainingPublic">
                                        Agree to NEXVIA™ mandatory technical training & audit standards
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5. PRODUCTS & DELIVERY CAPABILITY -->
                    <div class="mb-4">
                        <div class="section-title">
                            <span class="section-badge">5</span> Products & Delivery Capability
                        </div>
                        <label class="form-label fw-semibold small d-block">Products Capable of Handling</label>
                        <div class="row g-2 mb-3">
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
                                <div class="col-md-3 col-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="products_handled[]" value="{{ $pKey }}" id="pprod_{{ $pKey }}" checked>
                                        <label class="form-check-label small" for="pprod_{{ $pKey }}">{{ $pLabel }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row g-3">
                            <div class="col-md-2">
                                <label class="form-label fw-semibold small">Two-Wheeler</label>
                                <input type="number" name="vehicles_two_wheeler" class="form-control" value="2" min="0">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold small">Three-Wheeler</label>
                                <input type="number" name="vehicles_three_wheeler" class="form-control" value="1" min="0">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold small">Pickup / LCV</label>
                                <input type="number" name="vehicles_pickup_lcv" class="form-control" value="1" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Other Vehicles</label>
                                <input type="text" name="vehicles_other" class="form-control" placeholder="e.g. 1 Van">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Max Delivery Radius (KM)</label>
                                <input type="number" step="0.1" name="max_delivery_radius_km" class="form-control" value="25">
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="pdi_handover_sop" value="1" id="ppdiCheck" checked>
                                    <label class="form-check-label small" for="ppdiCheck">Pre-Delivery Inspection (PDI) / Handover SOP Compliance</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="otp_delivery_confirmation" value="1" id="potpCheck" checked>
                                    <label class="form-check-label small" for="potpCheck">OTP Delivery Confirmation System</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6. SECURITY STOCK DEPOSIT -->
                    <div class="mb-4">
                        <div class="section-title">
                            <span class="section-badge">6</span> Security Stock Deposit
                        </div>
                        <div class="card bg-light border p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h4 class="fw-bold text-dark mb-0">₹ 10,00,000</h4>
                                    <span class="text-muted small">Security Stock Deposit (Against Company Inventory)</span>
                                </div>
                                <div class="badge bg-success-subtle text-success p-2 fs-12">
                                    Refundable as per executed DSP Agreement
                                </div>
                            </div>
                            <ul class="list-unstyled mb-0 mt-3 small text-muted">
                                <li>✓ This is a security stock deposit and not a purchase of products.</li>
                                <li>✓ Company inventory remains property of NEXVIA™ / applicable company entity.</li>
                                <li>✓ I/We will maintain safe custody and proper records.</li>
                                <li>✓ I/We will report loss/damage immediately and permit regular stock audits.</li>
                            </ul>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Payment Reference / UTR Number (if deposited)</label>
                                <input type="text" name="deposit_transaction_reference" class="form-control" placeholder="e.g. UTR / IMPS reference">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Upload Deposit Payment Receipt / Cheque</label>
                                <input type="file" name="deposit_payment_proof" class="form-control" accept="image/*,.pdf">
                            </div>
                        </div>
                    </div>

                    <!-- 7. COMPANY BANK DETAILS -->
                    <div class="mb-4">
                        <div class="section-title">
                            <span class="section-badge">7</span> Company Bank Details
                        </div>
                        <div class="card bank-card p-3">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <span class="text-muted small d-block">Bank</span>
                                    <strong class="text-dark">IDFC FIRST BANK LIMITED</strong>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted small d-block">Account Name</span>
                                    <strong class="text-dark">DLS AGRO INFRAVENTURE PVT LTD</strong>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted small d-block">Account Number</span>
                                    <strong class="text-dark font-monospace">10048728156</strong>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted small d-block">IFSC Code</span>
                                    <strong class="text-dark font-monospace">IDFB0042281</strong>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted small d-block">Branch</span>
                                    <strong class="text-dark">Nashik</strong>
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">Important: Security Stock Deposit should be paid only to the above authorised company bank account.</small>

                            <div class="row align-items-center g-3 mt-2 pt-3 border-top">
                                <div class="col-md-4 text-center">
                                    <img src="{{ asset('images/dls_payment_qr.png') }}" alt="DLS UPI QR Code" class="img-fluid rounded border shadow-sm" style="max-height: 220px;">
                                    <span class="d-block micro text-muted mt-1">Official IDFC First Bank QR</span>
                                </div>
                                <div class="col-md-8">
                                    <h6 class="fw-bold text-dark mb-1">
                                        <i class="bx bx-qr-scan text-primary me-1"></i> Scan & Pay via any UPI App
                                    </h6>
                                    <p class="small text-muted mb-2">
                                        Scan this official QR code with any UPI app (Google Pay, PhonePe, Paytm, BHIM, Cred) to transfer your deposit.
                                    </p>
                                    <div class="p-2 bg-white rounded border font-monospace small mb-2">
                                        <span class="text-muted">UPI ID:</span> <strong class="text-danger">dlsagroin.09@idfcbank</strong>
                                    </div>
                                    <div class="p-2 bg-white rounded border font-monospace small">
                                        <span class="text-muted">Account Name:</span> <strong class="text-dark">DLS AGRO INFRAVENTURE PRIVATE LIMITED</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 8. APPLICANT DECLARATION -->
                    <div class="mb-4">
                        <div class="section-title">
                            <span class="section-badge">8</span> Applicant Declaration
                        </div>
                        <div class="form-check p-3 bg-light rounded border mb-3">
                            <input class="form-check-input ms-0 me-2" type="checkbox" name="declaration_agreed" value="1" id="declCheck" required checked>
                            <label class="form-check-label text-dark small" for="declCheck">
                                <strong>I/We declare that the information provided is true and correct.</strong> Submission of this application does not create a right to appointment. Final appointment is subject to verification, approval, execution of the DSP Agreement and fulfilment of company conditions.
                            </label>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Signatory Full Name *</label>
                                <input type="text" name="declaration_signature_name" class="form-control" required placeholder="Authorised Signatory Name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Upload Signature / Authority Letter (Optional)</label>
                                <input type="file" name="signature_file" class="form-control" accept="image/*,.pdf">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold py-3 mt-2 shadow">
                        <i class="bx bx-send me-1"></i> Submit DSP Application
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
