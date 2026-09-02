@extends('adminlayouts.vertical', ['title' => 'Service Requests'])

@section('title', 'Service & Warranty Requests Management')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Service & Warranty Requests</h4>
            <p class="text-muted small mb-0">Track product warranty claims, installation requests, and service tickets</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border border-light-subtle shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Ticket #</th>
                            <th>Customer</th>
                            <th>Booking Receipt</th>
                            <th>Subject & Service Type</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-primary-subtle text-primary font-monospace fw-bold">#{{ $req->ticket_number }}</span>
                                </td>
                                <td>
                                    <strong class="text-dark d-block fs-14">{{ $req->user->name ?? 'Customer' }}</strong>
                                    <span class="text-muted micro">{{ $req->user->phone ?? '' }}</span>
                                </td>
                                <td>
                                    <span class="font-monospace small">#{{ $req->booking->booking_number ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <strong class="text-dark d-block fs-13">{{ $req->subject }}</strong>
                                    <span class="badge bg-light text-dark micro">{{ ucfirst($req->service_type) }}</span>
                                </td>
                                <td>
                                    <span class="badge {{ $req->status === 'resolved' ? 'bg-success' : ($req->status === 'in_progress' ? 'bg-warning text-dark' : 'bg-danger') }}">
                                        {{ strtoupper(str_replace('_', ' ', $req->status)) }}
                                    </span>
                                </td>
                                <td class="small text-muted">{{ $req->created_at->format('d M, Y') }}</td>
                                <td class="pe-4 text-end">
                                    <form action="{{ route('admin.service.requests.status', $req->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                            <option value="open" {{ $req->status === 'open' ? 'selected' : '' }}>Open</option>
                                            <option value="in_progress" {{ $req->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="resolved" {{ $req->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        @if($requests->count() === 0)
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No active service or warranty tickets found.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
