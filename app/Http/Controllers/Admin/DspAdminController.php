<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\DspApprovedMail;
use App\Models\DspApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DspAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $search = trim((string)$request->query('q'));

        $query = DspApplication::query();

        if ($status !== 'all' && in_array($status, ['pending', 'under_review', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('applicant_name', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%")
                  ->orWhere('application_number', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('district', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%");
            });
        }

        $applications = $query->latest('id')->paginate(15)->withQueryString();

        $stats = [
            'total'        => DspApplication::count(),
            'pending'      => DspApplication::where('status', 'pending')->count(),
            'under_review' => DspApplication::where('status', 'under_review')->count(),
            'approved'     => DspApplication::where('status', 'approved')->count(),
            'rejected'     => DspApplication::where('status', 'rejected')->count(),
        ];

        return view('admin.dsp.index', compact('applications', 'status', 'search', 'stats'));
    }

    public function show($id)
    {
        $application = DspApplication::with('reviewer')->findOrFail($id);
        return view('admin.dsp.show', compact('application'));
    }

    public function create()
    {
        return view('admin.dsp.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'applicant_name'       => 'required|string|max:255',
            'mobile'               => 'required|string|max:20',
            'business_name'        => 'required|string|max:255',
            'district'             => 'nullable|string|max:100',
            'state'                => 'nullable|string|max:100',
            'deposit_payment_proof'=> 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
            'signature_file'       => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
        ]);

        $proofPath = null;
        if ($request->hasFile('deposit_payment_proof')) {
            $file = $request->file('deposit_payment_proof');
            $fileName = 'dsp_proof_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/dsp'), $fileName);
            $proofPath = 'uploads/dsp/' . $fileName;
        }

        $sigPath = null;
        if ($request->hasFile('signature_file')) {
            $file = $request->file('signature_file');
            $fileName = 'dsp_sig_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/dsp'), $fileName);
            $sigPath = 'uploads/dsp/' . $fileName;
        }

        $appNumber = 'DSP-' . date('Y') . '-' . strtoupper(Str::random(6));

        $application = DspApplication::create([
            'application_number'               => $appNumber,
            'application_date'                 => $request->application_date ?: now()->toDateString(),
            'preferred_territory_area'         => $request->preferred_territory_area,
            'pincodes'                         => $request->pincodes,
            'district'                         => $request->district,
            'state'                            => $request->state,
            'applicant_name'                   => $request->applicant_name,
            'father_or_spouse_name'            => $request->father_or_spouse_name,
            'date_of_birth'                    => $request->date_of_birth,
            'mobile'                           => $request->mobile,
            'whatsapp'                         => $request->whatsapp,
            'email'                            => $request->email,
            'residential_address'              => $request->residential_address,
            'residential_pincode'              => $request->residential_pincode,
            'business_name'                    => $request->business_name,
            'business_constitution'            => $request->business_constitution ?? 'proprietorship',
            'business_constitution_other'      => $request->business_constitution_other,
            'year_established'                 => $request->year_established,
            'pan'                              => $request->pan ? strtoupper($request->pan) : null,
            'gstin'                            => $request->gstin ? strtoupper($request->gstin) : null,
            'existing_business_activity'       => $request->existing_business_activity,
            'years_of_experience'              => $request->years_of_experience,
            'premises_type'                    => $request->premises_type ?? 'owned',
            'complete_address'                 => $request->complete_address,
            'premises_pincode'                 => $request->premises_pincode,
            'total_area_sqft'                  => $request->total_area_sqft,
            'frontage_feet'                    => $request->frontage_feet,
            'available_facilities'             => $request->available_facilities ?? [],
            'presently_operate_service_centre' => $request->has('presently_operate_service_centre'),
            'technicians_count'                => (int)$request->technicians_count,
            'ev_technician_status'             => $request->ev_technician_status ?? 'no',
            'electrical_technician_status'     => $request->electrical_technician_status ?? 'no',
            'home_appliance_technician_status' => $request->home_appliance_technician_status ?? 'no',
            'agree_to_nexvia_training'         => $request->has('agree_to_nexvia_training'),
            'products_handled'                 => $request->products_handled ?? [],
            'vehicles_two_wheeler'             => (int)$request->vehicles_two_wheeler,
            'vehicles_three_wheeler'           => (int)$request->vehicles_three_wheeler,
            'vehicles_pickup_lcv'              => (int)$request->vehicles_pickup_lcv,
            'vehicles_other'                   => $request->vehicles_other,
            'max_delivery_radius_km'           => $request->max_delivery_radius_km,
            'pdi_handover_sop'                 => $request->has('pdi_handover_sop'),
            'otp_delivery_confirmation'        => $request->has('otp_delivery_confirmation'),
            'security_deposit_amount'          => 1000000.00,
            'deposit_terms_agreed'             => true,
            'deposit_payment_status'           => $request->deposit_payment_status ?? 'pending',
            'deposit_transaction_reference'    => $request->deposit_transaction_reference,
            'deposit_payment_proof'            => $proofPath,
            'declaration_agreed'               => true,
            'declaration_date'                 => now()->toDateString(),
            'declaration_signature_name'       => $request->declaration_signature_name ?? $request->applicant_name,
            'signature_file'                   => $sigPath,
            'status'                           => $request->status ?? 'pending',
            'admin_notes'                      => $request->admin_notes,
            'reviewed_by'                      => Auth::guard('admin')->id(),
            'reviewed_at'                      => now(),
        ]);

        return redirect()->route('admin.dsp.show', $application->id)
            ->with('success', "DSP Partner application {$appNumber} created successfully!");
    }

    public function updateStatus(Request $request, $id)
    {
        $application = DspApplication::findOrFail($id);

        $request->validate([
            'status'                 => 'required|in:pending,under_review,approved,rejected',
            'admin_notes'            => 'nullable|string',
            'deposit_payment_status' => 'nullable|in:pending,paid,verified',
            'email'                  => 'nullable|email|max:255',
            'password'               => 'nullable|string|min:6',
        ]);

        $previousStatus = $application->status;
        $application->status = $request->status;
        $application->admin_notes = $request->admin_notes;
        if ($request->filled('deposit_payment_status')) {
            $application->deposit_payment_status = $request->deposit_payment_status;
        }
        if ($request->filled('email')) {
            $application->email = trim($request->email);
        }
        $application->reviewed_by = Auth::guard('admin')->id();
        $application->reviewed_at = now();

        $emailNotice = '';

        if ($request->status === 'approved') {
            // Determine or generate password
            if ($request->filled('password')) {
                $plainPassword = $request->password;
                $application->password = Hash::make($plainPassword);
            } elseif (empty($application->password)) {
                $plainPassword = 'Dsp@' . rand(100000, 999999);
                $application->password = Hash::make($plainPassword);
            } else {
                // If already had a password and no new password passed, generate a new temporary password so it can be emailed
                $plainPassword = 'Dsp@' . rand(100000, 999999);
                $application->password = Hash::make($plainPassword);
            }

            $application->save();

            // Send approval email with login details
            if (!empty($application->email)) {
                $fromEmail = config('mail.from.address', 'nexviadls@gmail.com');
                try {
                    Mail::to($application->email)->send(new DspApprovedMail($application, $plainPassword));
                    $emailNotice = " Login credentials (Password: {$plainPassword}) were sent to {$application->email} from {$fromEmail}.";
                } catch (\Throwable $e) {
                    Log::error("Failed sending DSP approval email to {$application->email}: " . $e->getMessage());
                    $emailNotice = " (Note: Could not send email: " . $e->getMessage() . ". Generated password: {$plainPassword})";
                }
            } else {
                $emailNotice = " (Note: No email on file. Portal password is: {$plainPassword})";
            }
        } else {
            $application->save();
        }

        return back()->with('success', "Application status updated to " . strtoupper(str_replace('_', ' ', $application->status)) . "!" . $emailNotice);
    }

    public function destroy($id)
    {
        $application = DspApplication::findOrFail($id);
        $application->delete();

        return redirect()->route('admin.dsp.index')
            ->with('success', "Application {$application->application_number} deleted successfully!");
    }

    public function updatePassword(Request $request, $id)
    {
        $application = DspApplication::findOrFail($id);

        $request->validate([
            'password' => 'required|string|min:6',
            'email'    => 'nullable|email|max:255',
        ]);

        if ($request->filled('email')) {
            $application->email = trim($request->email);
        }

        $plainPassword = $request->password;
        $application->password = Hash::make($plainPassword);
        $application->save();

        $emailNotice = '';
        if (!empty($application->email)) {
            $fromEmail = config('mail.from.address', 'nexviadls@gmail.com');
            try {
                Mail::to($application->email)->send(new DspApprovedMail($application, $plainPassword));
                $emailNotice = " and login details emailed to {$application->email} from {$fromEmail}";
            } catch (\Throwable $e) {
                Log::error("Failed sending DSP password update email to {$application->email}: " . $e->getMessage());
                $emailNotice = " (Email dispatch error: " . $e->getMessage() . ")";
            }
        }

        return back()->with('success', "Portal login password updated successfully for {$application->applicant_name} (Mobile: {$application->mobile}){$emailNotice}!");
    }
}
