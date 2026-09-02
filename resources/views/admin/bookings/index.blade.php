@extends('adminlayouts.vertical')

@section('title', 'Bookings & 60-Day Balances Management')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-extrabold text-dark mb-1">Customer Bookings & 60-Day Balances</h4>
            <p class="text-muted small mb-0">Track active 20% booking receipts, 60-day balance timelines, and customer payment statuses</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Receipt #</th>
                            <th>Customer</th>
                            <th>Product Name</th>
                            <th>20% Paid</th>
                            <th>80% Balance Due</th>
                            <th>60-Day Due Date</th>
                            <th>Payment Status</th>
                            <th>Transfer Status</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookings as $booking)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-primary-subtle text-primary font-monospace fs-13 fw-bold">#{{ $booking->booking_number }}</span>
                                </td>
                                <td>
                                    <strong class="text-dark d-block fs-14">{{ $booking->customer_name }}</strong>
                                    <span class="text-muted micro">{{ $booking->customer_phone }}</span>
                                </td>
                                <td>
                                    <span class="fs-13 text-dark font-monospace text-wrap d-inline-block" style="max-width: 200px;">{{ $booking->product_name }}</span>
                                </td>
                                <td class="text-success fw-extrabold fs-14">₹{{ number_format($booking->booking_amount, 2) }}</td>
                                <td class="text-danger fw-bold fs-14">₹{{ number_format($booking->balance_amount, 2) }}</td>
                                <td>
                                    <span class="fs-13 text-dark d-block">{{ \Carbon\Carbon::parse($booking->balance_due_date)->format('d M, Y') }}</span>
                                    @if($booking->payment_status !== 'fully_paid')
                                        <span class="micro text-warning fw-bold">({{ $booking->days_remaining }} days remaining)</span>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->payment_status === 'fully_paid')
                                        <span class="badge bg-success text-white px-3 py-1 fs-12">Fully Paid</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-3 py-1 fs-12">20% Paid (Balance Due)</span>
                                    @endif
                                </td>
                                <td>
                                    @if($booking->transfer_status === 'transferred')
                                        <span class="badge bg-purple text-white px-3 py-1 fs-12">Transferred</span>
                                    @else
                                        <span class="badge bg-light text-secondary px-3 py-1 fs-12">Original Owner</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-soft-primary px-3 rounded-pill">Inspect Receipt</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top">
                {{ $bookings->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
