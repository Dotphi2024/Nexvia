<?php

namespace App\Http\Controllers;

use App\Models\DspApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DspPublicController extends Controller
{
    /**
     * Show the public DSP Application Form
     */
    public function create()
    {
        return view('dsp.apply');
    }

    /**
     * Store submitted public application
     */
    public function store(Request $request)
    {
        $request->validate([
            'applicant_name'       => 'required|string|max:255',
            'mobile'               => 'required|string|max:20',
            'business_name'        => 'required|string|max:255',
            'declaration_agreed'   => 'required',
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
            'application_date'                 => now()->toDateString(),
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
            'deposit_payment_status'           => 'pending',
            'deposit_transaction_reference'    => $request->deposit_transaction_reference,
            'deposit_payment_proof'            => $proofPath,
            'declaration_agreed'               => true,
            'declaration_date'                 => now()->toDateString(),
            'declaration_signature_name'       => $request->declaration_signature_name ?? $request->applicant_name,
            'signature_file'                   => $sigPath,
            'status'                           => 'pending',
        ]);

        // Send acknowledgement email to applicant & notification to admin via SMTP
        if (!empty($application->email)) {
            try {
                $appName = config('mail.from.name', 'NEXVIA');
                Mail::raw("Dear {$application->applicant_name},\n\nThank you for submitting your application to become an Authorised Delivery & Service Partner (DSP) with {$appName}.\n\nApplication Number: {$application->application_number}\nBusiness Name: {$application->business_name}\nTerritory: {$application->preferred_territory_area} ({$application->district}, {$application->state})\n\nOur team is reviewing your application and documentation. You will receive updates shortly.\n\nBest regards,\n{$appName} Partner Operations", function ($m) use ($application, $appName) {
                    $m->to($application->email)
                      ->subject("{$appName} DSP Application Received - {$application->application_number}");
                });
            } catch (\Throwable $e) {
                \Log::warning("DSP applicant email failed: " . $e->getMessage());
            }
        }

        // Notify company admin inbox
        try {
            $adminEmail = config('mail.from.address', 'nexviadls@gmail.com');
            $appName = config('mail.from.name', 'NEXVIA');
            Mail::raw("New DSP Application Received!\n\nApplication Number: {$application->application_number}\nApplicant: {$application->applicant_name}\nBusiness: {$application->business_name}\nMobile: {$application->mobile}\nEmail: " . ($application->email ?: 'N/A') . "\nDistrict: {$application->district}, {$application->state}\nTerritory: {$application->preferred_territory_area}\n\nPlease review this application in the admin portal.", function ($m) use ($adminEmail, $application, $appName) {
                $m->to($adminEmail)
                  ->subject("New DSP Partner Application - {$application->application_number} ({$application->applicant_name})");
            });
        } catch (\Throwable $e) {
            \Log::warning("Admin DSP notification email failed: " . $e->getMessage());
        }

        return redirect()->route('dsp.success', ['application' => $application->application_number]);
    }

    public function success(Request $request)
    {
        $appNumber = $request->query('application');
        return view('dsp.success', compact('appNumber'));
    }

    /**
     * Return matching DSP partners servicing a pincode/district
     */
    public function availableByPincode(Request $request)
    {
        $pincode  = $request->query('pincode', $request->input('pincode'));
        $district = $request->query('district', $request->input('district'));
        $state    = $request->query('state', $request->input('state'));

        $matchingService = app(\App\Services\DspMatchingService::class);
        $dsps = $matchingService->findAvailableDsps($pincode, $district, $state);

        $formatted = $dsps->map(function ($dsp) {
            return [
                'id'                 => $dsp->id,
                'business_name'      => $dsp->business_name ?: $dsp->applicant_name,
                'applicant_name'     => $dsp->applicant_name,
                'mobile'             => $dsp->mobile,
                'district'           => $dsp->district,
                'state'              => $dsp->state,
                'premises_address'   => $dsp->complete_address,
                'premises_pincode'   => $dsp->premises_pincode,
                'match_type'         => $dsp->match_type ?? 'regional',
                'match_label'        => $dsp->match_label ?? 'Delivery Partner',
                'technicians_count'  => (int)$dsp->technicians_count,
                'vehicles_count'     => $dsp->total_vehicles,
            ];
        });

        return response()->json([
            'status' => true,
            'count'  => $formatted->count(),
            'dsps'   => $formatted,
        ]);
    }
}
