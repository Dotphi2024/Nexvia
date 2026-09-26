<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\DspApplication;
use App\Services\DspMatchingService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ServiceRequestController extends Controller
{
    /**
     * List all service / problem requests with DSP allocation & attended tracking
     */
    public function index(Request $request)
    {
        $query = ServiceRequest::with(['user', 'booking.product', 'dsp']);

        // Filter by Attended status (all, yes, no)
        if ($request->filled('attended')) {
            if ($request->attended === 'yes') {
                $query->where('is_attended', true);
            } elseif ($request->attended === 'no') {
                $query->where('is_attended', false);
            }
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by DSP assignment
        if ($request->filled('dsp_id')) {
            if ($request->dsp_id === 'unassigned') {
                $query->whereNull('dsp_id');
            } else {
                $query->where('dsp_id', $request->dsp_id);
            }
        }

        // Filter by Service Type / Problem Type
        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        // Search query
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('ticket_number', 'like', "%{$s}%")
                  ->orWhere('subject', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%")
                  ->orWhere('customer_phone', 'like', "%{$s}%")
                  ->orWhere('city', 'like', "%{$s}%")
                  ->orWhere('pincode', 'like', "%{$s}%")
                  ->orWhereHas('user', function ($uQ) use ($s) {
                      $uQ->where('name', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%");
                  })
                  ->orWhereHas('dsp', function ($dQ) use ($s) {
                      $dQ->where('business_name', 'like', "%{$s}%")
                         ->orWhere('applicant_name', 'like', "%{$s}%")
                         ->orWhere('mobile', 'like', "%{$s}%");
                  });
            });
        }

        $requests = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'             => ServiceRequest::count(),
            'assigned_dsp'      => ServiceRequest::whereNotNull('dsp_id')->count(),
            'unassigned'        => ServiceRequest::whereNull('dsp_id')->count(),
            'attended'          => ServiceRequest::where('is_attended', true)->count(),
            'pending_attention' => ServiceRequest::where('is_attended', false)->where('status', '!=', 'resolved')->count(),
            'resolved'          => ServiceRequest::where('status', 'resolved')->count(),
        ];

        $dsps = DspApplication::where('status', 'approved')->orderBy('business_name')->get();

        return view('admin.service_requests.index', compact('requests', 'stats', 'dsps'));
    }

    /**
     * Assign or reassign DSP Partner to a service ticket
     */
    public function assignDsp(Request $request, $id)
    {
        $sr = ServiceRequest::findOrFail($id);

        if ($request->has('auto_match') && $request->auto_match) {
            $matchingService = app(DspMatchingService::class);
            $matchedDsp = $matchingService->getBestMatchingDsp($sr->pincode, $sr->city, $sr->state);

            if (!$matchedDsp) {
                return back()->with('error', "No approved DSP found servicing area PIN {$sr->pincode} / {$sr->city}.");
            }

            $sr->dsp_id = $matchedDsp->id;
            $sr->save();

            return back()->with('success', "Service request #{$sr->ticket_number} successfully allocated to local partner '{$matchedDsp->business_name}'.");
        }

        $request->validate([
            'dsp_id' => 'required|exists:dsp_applications,id',
        ]);

        $dsp = DspApplication::findOrFail($request->dsp_id);
        $sr->dsp_id = $dsp->id;
        $sr->save();

        return back()->with('success', "Service request #{$sr->ticket_number} assigned to Authorised DSP '{$dsp->business_name}'.");
    }

    /**
     * Update status and attendance of a service request
     */
    public function updateStatus(Request $request, $id)
    {
        $sr = ServiceRequest::findOrFail($id);
        $request->validate([
            'status'            => 'required|string|in:open,attended,in_progress,resolved,cancelled',
            'is_attended'       => 'nullable|boolean',
            'attended_by_name'  => 'nullable|string|max:255',
            'attended_by_phone' => 'nullable|string|max:20',
            'dsp_notes'         => 'nullable|string|max:2000',
            'resolution_notes'  => 'nullable|string|max:2000',
        ]);

        $data = [
            'status' => $request->status,
        ];

        if ($request->has('is_attended')) {
            $data['is_attended'] = (bool) $request->is_attended;
            if ($data['is_attended'] && !$sr->is_attended) {
                $data['attended_at'] = now();
            }
        }

        // If status moved to attended or in_progress, ensure is_attended is true
        if (in_array($request->status, ['attended', 'in_progress']) && !$sr->is_attended) {
            $data['is_attended'] = true;
            $data['attended_at'] = now();
        }

        if ($request->status === 'resolved') {
            $data['is_attended'] = true;
            $data['resolved_at'] = now();
            if ($request->filled('resolution_notes')) {
                $data['resolution_notes'] = $request->resolution_notes;
            }
        }

        if ($request->filled('attended_by_name')) {
            $data['attended_by_name'] = $request->attended_by_name;
        }
        if ($request->filled('attended_by_phone')) {
            $data['attended_by_phone'] = $request->attended_by_phone;
        }
        if ($request->filled('dsp_notes')) {
            $data['dsp_notes'] = $request->dsp_notes;
        }

        $sr->update($data);

        return back()->with('success', "Service ticket #{$sr->ticket_number} status updated successfully.");
    }
}
