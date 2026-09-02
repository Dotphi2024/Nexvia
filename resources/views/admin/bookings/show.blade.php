@extends('adminlayouts.vertical')

@section('title', 'Booking Details #' . $booking->booking_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Booking Receipt #{{ $booking->booking_number }}</h4>
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">← Back to List</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Booking Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th class="w-30">Product Name</th>
                            <td class="fw-bold">{{ $booking->product_name }}</td>
                        </tr>
                        <tr>
                            <th>Model Code</th>
                            <td class="font-monospace">{{ $booking->model_code }}</td>
                        </tr>
                        <tr>
                            <th>Total MRP</th>
                            <td class="fw-bold">₹{{ number_format($booking->mrp, 2) }}</td>
                        </tr>
                        <tr>
                            <th>20% Booking Amount</th>
                            <td class="text-success fw-bold">₹{{ number_format($booking->booking_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>80% Balance Amount</th>
                            <td class="text-danger fw-bold">₹{{ number_format($booking->balance_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Booking Date</th>
                            <td>{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M, Y') }}</td>
                        </tr>
                        <tr>
                            <th>60-Day Balance Due Date</th>
                            <td class="fw-bold text-primary">{{ \Carbon\Carbon::parse($booking->balance_due_date)->format('d M, Y') }}</td>
                        </tr>
                        <tr>
                            <th>Non-Refundable Policy Agreed</th>
                            <td><span class="badge bg-success">Yes (Accepted)</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Transfer History Audit Trail -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Transfer Audit History</h5>
            </div>
            <div class="card-body p-0">
                @if($booking->transfers->count() > 0)
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Transferred From</th>
                                <th>Transferred To</th>
                                <th>Phone</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($booking->transfers as $tr)
                                <tr>
                                    <td>{{ $tr->fromUser->name ?? 'Original Customer' }}</td>
                                    <td>{{ $tr->to_name }}</td>
                                    <td>{{ $tr->to_phone }}</td>
                                    <td>{{ $tr->transferred_at ? \Carbon\Carbon::parse($tr->transferred_at)->format('d M, Y H:i') : 'Pending' }}</td>
                                    <td><span class="badge bg-success">{{ strtoupper($tr->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted p-3 mb-0">No transfers recorded for this booking receipt.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Sidebar Update Form -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Update Status</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.bookings.update.status', $booking->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select">
                            <option value="paid" {{ $booking->payment_status === 'paid' ? 'selected' : '' }}>20% Paid (Balance Due)</option>
                            <option value="fully_paid" {{ $booking->payment_status === 'fully_paid' ? 'selected' : '' }}>Fully Paid</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Booking / Delivery Status</label>
                        <select name="booking_status" class="form-select">
                            <option value="booked" {{ $booking->booking_status === 'booked' ? 'selected' : '' }}>Booked</option>
                            <option value="balance_paid" {{ $booking->booking_status === 'balance_paid' ? 'selected' : '' }}>Balance Paid</option>
                            <option value="completed" {{ $booking->booking_status === 'completed' ? 'selected' : '' }}>Completed / Delivered</option>
                            <option value="cancelled" {{ $booking->booking_status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Update Booking</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
