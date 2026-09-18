@extends('dsp.layouts.portal')

@section('title', 'Territory Deliveries – DSP Partner Portal')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Territory Deliveries Management</h4>
        <p class="text-muted small mb-0">
            Orders and product bookings in your serviced territory PIN codes: <code>{{ $dsp->pincodes ?: 'All Territory' }}</code>.
            Earn <strong>5% cash commission</strong> automatically upon completing each delivery!
        </p>
    </div>

    <!-- Status Tabs -->
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('dsp.deliveries', ['status' => 'all']) }}" class="btn btn-sm {{ $status === 'all' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-3 fw-bold">
            All Deliveries
        </a>
        <a href="{{ route('dsp.deliveries', ['status' => 'pending']) }}" class="btn btn-sm {{ $status === 'pending' ? 'btn-warning text-dark' : 'btn-outline-secondary' }} rounded-3 fw-bold">
            Pending Dispatch
        </a>
        <a href="{{ route('dsp.deliveries', ['status' => 'out_for_delivery']) }}" class="btn btn-sm {{ $status === 'out_for_delivery' ? 'btn-info text-white' : 'btn-outline-secondary' }} rounded-3 fw-bold">
            Out for Delivery
        </a>
        <a href="{{ route('dsp.deliveries', ['status' => 'delivered']) }}" class="btn btn-sm {{ $status === 'delivered' ? 'btn-success text-white' : 'btn-outline-secondary' }} rounded-3 fw-bold">
            Successfully Delivered
        </a>
    </div>
</div>

<!-- Search Bar -->
<div class="card-dsp p-3 mb-4">
    <form action="{{ route('dsp.deliveries') }}" method="GET" class="row g-2 align-items-center">
        <input type="hidden" name="status" value="{{ $status }}">
        <div class="col-md-10">
            <div class="input-group">
                <span class="input-group-text bg-light text-muted border-end-0">
                    <iconify-icon icon="solar:magnifer-bold"></iconify-icon>
                </span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by Order #, Customer Name, Mobile, or PIN code..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100 fw-bold">Search</button>
        </div>
    </form>
</div>

<!-- Deliveries List -->
<div class="card-dsp p-4">
    @if($deliveries->count() > 0)
        <div class="table-responsive">
            <table class="table table-dsp align-middle mb-0">
                <thead>
                    <tr>
                        <th>Order / Tracking #</th>
                        <th>Product & Price</th>
                        <th>Customer Details</th>
                        <th>Delivery Address</th>
                        <th>5% Commission</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deliveries as $delivery)
                        @php
                            $booking = $delivery->booking;
                            $order = $delivery->order;
                            $productPrice = (float)($booking?->mrp ?? $order?->total_amount ?? 0);
                            $commission = round($productPrice * 0.05, 2);
                            $custName = $booking?->customer_name ?? $order?->customer_name ?? 'Customer';
                            $custPhone = $booking?->customer_phone ?? $order?->customer_phone ?? '';
                            $pincode = $booking?->pincode ?? $order?->pincode ?? '';
                            $address = $booking?->shipping_address ?? $order?->shipping_address ?? '';
                        @endphp
                        <tr>
                            <td>
                                <span class="fw-bold font-monospace text-primary small d-block">#{{ $booking?->booking_number ?? $delivery->tracking_number }}</span>
                                <span class="micro text-muted font-monospace">Track: {{ $delivery->tracking_number }}</span>
                                <span class="micro text-muted d-block">{{ $delivery->created_at->format('d M, Y') }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark small">{{ $booking?->product_name ?? 'NEXVIA Product' }}</div>
                                @if($booking?->model_code)
                                    <span class="badge bg-light text-dark micro font-monospace">{{ $booking->model_code }}</span>
                                @endif
                                <div class="small fw-semibold text-secondary mt-1">MRP: ₹{{ number_format($productPrice, 2) }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark small">{{ $custName }}</div>
                                @if($custPhone)
                                    <a href="tel:{{ $custPhone }}" class="btn btn-sm btn-outline-primary py-0 px-2 micro mt-1 d-inline-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:phone-bold"></iconify-icon> Call {{ $custPhone }}
                                    </a>
                                @endif
                            </td>
                            <td style="max-width: 200px;">
                                <div class="small text-dark text-truncate" title="{{ $address }}">{{ $address }}</div>
                                <span class="badge bg-secondary-subtle text-secondary micro mt-1">PIN: {{ $pincode }}</span>
                            </td>
                            <td>
                                <span class="badge-commission">
                                    +₹{{ number_format($commission, 2) }}
                                </span>
                                <div class="micro text-muted mt-1">
                                    @if($delivery->stage === 'delivered')
                                        <span class="text-success fw-bold"><iconify-icon icon="solar:check-circle-bold"></iconify-icon> Credited</span>
                                    @else
                                        On Delivery
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($delivery->stage === 'delivered')
                                    <span class="badge bg-success text-white px-3 py-2">DELIVERED</span>
                                @elseif($delivery->stage === 'out_for_delivery')
                                    <span class="badge bg-info text-white px-3 py-2">OUT FOR DELIVERY</span>
                                @else
                                    <span class="badge bg-warning text-dark px-3 py-2">ASSIGNED</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('dsp.deliveries.show', $delivery->id) }}" class="btn btn-outline-primary btn-sm fw-bold">
                                        View
                                    </a>
                                    @if($delivery->stage !== 'delivered')
                                        <button type="button" class="btn btn-success btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#updateModal{{ $delivery->id }}">
                                            Update
                                        </button>
                                    @endif
                                </div>

                                <!-- Quick Update Status Modal -->
                                @if($delivery->stage !== 'delivered')
                                    <div class="modal fade" id="updateModal{{ $delivery->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content rounded-4 border-0 shadow-lg">
                                                <div class="modal-header bg-dark text-white">
                                                    <h6 class="modal-title fw-bold">
                                                        <iconify-icon icon="solar:delivery-bold" class="text-warning me-1 align-middle"></iconify-icon>
                                                        Update Delivery Status – #{{ $booking?->booking_number ?? $delivery->tracking_number }}
                                                    </h6>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('dsp.deliveries.status', $delivery->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body p-4">
                                                        <div class="alert alert-success bg-opacity-10 border-success p-3 rounded-3 mb-3">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="small text-muted">Earn Commission on Delivery:</span>
                                                                <strong class="text-success fs-5">+₹{{ number_format($commission, 2) }} (5%)</strong>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-bold text-dark small">Select New Status *</label>
                                                            <select name="stage" class="form-select" required>
                                                                @if($delivery->stage !== 'out_for_delivery')
                                                                    <option value="out_for_delivery">Mark Out for Delivery (In Transit)</option>
                                                                @endif
                                                                <option value="delivered" selected>Mark as DELIVERED (Credit 5% Commission)</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold text-dark small">Delivery OTP / Confirmation Reference (Optional)</label>
                                                            <input type="text" name="delivery_otp" class="form-control font-monospace" placeholder="Enter customer delivery OTP or signature ref">
                                                        </div>

                                                        <div class="mb-2">
                                                            <label class="form-label fw-semibold text-dark small">Delivery Notes</label>
                                                            <textarea name="delivery_notes" class="form-control" rows="2" placeholder="e.g. Handed over to customer along with PDI checklist and invoice"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success btn-sm fw-bold px-4">
                                                            Save Status & Process
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $deliveries->links() }}
        </div>
    @else
        <div class="text-center py-5 text-muted">
            <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="fs-1 text-muted opacity-50 mb-2"></iconify-icon>
            <h6 class="fw-bold">No deliveries found.</h6>
            <p class="small text-secondary mb-0">Check back later or adjust your search / status filter.</p>
        </div>
    @endif
</div>
@endsection
