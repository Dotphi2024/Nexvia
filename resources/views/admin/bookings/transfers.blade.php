@extends('adminlayouts.vertical')

@section('title', 'Booking Transfers Audit Log')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-extrabold text-dark mb-1">Transferable Booking Receipts Audit Log</h4>
            <p class="text-muted small mb-0">Complete audit trail of all receipt ownership transfers verified via recipient mobile OTP</p>
        </div>
    </div>

    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Receipt #</th>
                            <th>Product Details</th>
                            <th>Original Owner (From)</th>
                            <th>New Recipient (To)</th>
                            <th>Recipient Mobile</th>
                            <th>Transfer OTP</th>
                            <th>Audit Status</th>
                            <th class="pe-4 text-end">Transferred Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transfers as $tr)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-primary-subtle text-primary font-monospace fs-13 fw-bold">#{{ $tr->booking->booking_number }}</span>
                                </td>
                                <td>
                                    <strong class="text-dark d-block fs-13">{{ $tr->booking->product_name }}</strong>
                                </td>
                                <td>{{ $tr->fromUser->name ?? 'User' }}</td>
                                <td><strong class="text-dark fs-13">{{ $tr->to_name }}</strong></td>
                                <td class="font-monospace text-muted small">{{ $tr->to_phone }}</td>
                                <td><span class="badge bg-light text-dark font-monospace fs-12">{{ $tr->transfer_otp }}</span></td>
                                <td>
                                    @if($tr->status === 'completed')
                                        <span class="badge bg-success text-white px-3 py-1 fs-12">COMPLETED</span>
                                    @elseif($tr->status === 'pending')
                                        <span class="badge bg-warning text-dark px-3 py-1 fs-12">PENDING OTP</span>
                                    @else
                                        <span class="badge bg-secondary px-3 py-1 fs-12">{{ strtoupper($tr->status) }}</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end text-muted small">
                                    {{ $tr->transferred_at ? \Carbon\Carbon::parse($tr->transferred_at)->format('d M, Y H:i') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top">
                {{ $transfers->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
