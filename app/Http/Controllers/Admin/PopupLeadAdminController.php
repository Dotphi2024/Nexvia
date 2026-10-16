<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PopupLead;
use App\Models\PopupSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PopupLeadAdminController extends Controller
{
    /**
     * Display a listing of popup leads and configuration.
     */
    public function index(Request $request)
    {
        $query = PopupLead::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('mobile', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('source_url', 'LIKE', "%{$search}%")
                  ->orWhere('admin_notes', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $leads = $query->latest()->paginate(20)->withQueryString();

        // Metrics & KPI Stats
        $stats = [
            'total'     => PopupLead::count(),
            'today'     => PopupLead::today()->count(),
            'pending'   => PopupLead::pending()->count(),
            'contacted' => PopupLead::whereIn('status', ['contacted', 'interested'])->count(),
            'converted' => PopupLead::converted()->count(),
            'closed'    => PopupLead::where('status', 'closed')->count(),
        ];

        $settings = PopupSetting::getSettings();

        return view('admin.popup_leads.index', compact('leads', 'stats', 'settings'));
    }

    /**
     * Update lead status and admin notes.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status'      => 'required|in:pending,contacted,interested,converted,closed',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $lead = PopupLead::findOrFail($id);

        $updates = [
            'status'      => $request->input('status'),
            'admin_notes' => $request->input('admin_notes'),
        ];

        if ($request->input('status') !== 'pending' && is_null($lead->contacted_at)) {
            $updates['contacted_at'] = now();
        }

        $lead->update($updates);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => true,
                'message' => 'Lead updated successfully.',
                'lead'    => $lead,
            ]);
        }

        return redirect()->back()->with('success', "Lead for '{$lead->name}' updated successfully.");
    }

    /**
     * Delete a popup lead.
     */
    public function destroy($id)
    {
        $lead = PopupLead::findOrFail($id);
        $leadName = $lead->name;
        $lead->delete();

        return redirect()->back()->with('success', "Enquiry lead for '{$leadName}' was deleted.");
    }

    /**
     * Update popup timing and content configuration.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'is_enabled'      => 'nullable|boolean',
            'delay_seconds'   => 'required|integer|min:1|max:120',
            'title'           => 'required|string|max:255',
            'subtitle'        => 'nullable|string|max:1000',
            'button_text'     => 'required|string|max:100',
            'success_message' => 'nullable|string|max:1000',
            'reappear_hours'  => 'required|integer|min:0|max:720',
        ]);

        $settings = PopupSetting::getSettings();

        $settings->update([
            'is_enabled'      => $request->has('is_enabled') ? (bool) $request->input('is_enabled') : false,
            'delay_seconds'   => (int) $request->input('delay_seconds'),
            'title'           => trim($request->input('title')),
            'subtitle'        => $request->input('subtitle') ? trim($request->input('subtitle')) : null,
            'button_text'     => trim($request->input('button_text')),
            'success_message' => $request->input('success_message') ? trim($request->input('success_message')) : null,
            'reappear_hours'  => (int) $request->input('reappear_hours'),
        ]);

        return redirect()->back()->with('success', 'Popup configuration saved successfully.');
    }

    /**
     * Export leads as CSV format for call center / telecalling operations.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = PopupLead::query();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $leads = $query->latest()->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="nexvia_popup_leads_' . date('Y-m-d_His') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($leads) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['ID', 'Name', 'Mobile', 'Email', 'Status', 'Source URL', 'IP Address', 'Admin Notes', 'Submitted At', 'Contacted At']);

            foreach ($leads as $lead) {
                fputcsv($handle, [
                    $lead->id,
                    $lead->name,
                    $lead->mobile,
                    $lead->email ?? '',
                    strtoupper($lead->status),
                    $lead->source_url ?? '',
                    $lead->ip_address ?? '',
                    $lead->admin_notes ?? '',
                    $lead->created_at?->format('Y-m-d H:i:s') ?? '',
                    $lead->contacted_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
