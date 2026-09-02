<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function index()
    {
        $requests = ServiceRequest::with(['user', 'booking'])->latest()->paginate(15);
        return view('admin.service_requests.index', compact('requests'));
    }

    public function updateStatus(Request $request, $id)
    {
        $sr = ServiceRequest::findOrFail($id);
        $request->validate(['status' => 'required|string']);
        $sr->update(['status' => $request->status]);

        return back()->with('success', 'Service ticket status updated successfully!');
    }
}
