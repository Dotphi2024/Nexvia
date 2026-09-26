<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use App\Models\ServiceRequest;
use App\Models\Installation;
use App\Models\Booking;
use App\Models\DspApplication;
use App\Services\DspMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WarrantyAndServiceApiController extends Controller
{
    protected function resolveUser(Request $request)
    {
        $user = $request->user('customer') ?? $request->get('authenticated_customer') ?? $request->user();
        if (!$user) {
            $bodyJson = json_decode($request->getContent(), true) ?? [];
            $token = $request->bearerToken()
                ?? $request->input('api_token')
                ?? $request->input('token')
                ?? $request->header('api_token')
                ?? $request->header('token')
                ?? ($bodyJson['api_token'] ?? null)
                ?? ($bodyJson['token'] ?? null);

            if (!empty($token)) {
                $user = \App\Models\Customer::where('api_token', $token)->first();
            }
        }
        return $user;
    }

    /**
     * GET /api/customer/warranties
     * List automatic warranties registered for customer.
     */
    public function warranties(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required.'], 401);
        }

        $warranties = Warranty::where('user_id', $user->id)
            ->with('product')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($w) {
                return [
                    'id'            => $w->id,
                    'product_id'    => $w->product_id,
                    'product_name'  => $w->product ? $w->product->name : 'NEXVIA Product',
                    'model_code'    => $w->product ? $w->product->model_code : null,
                    'serial_number' => $w->serial_number,
                    'purchase_date' => $w->purchase_date->format('Y-m-d'),
                    'warranty_start'=> $w->warranty_start->format('Y-m-d'),
                    'warranty_end'  => $w->warranty_end->format('Y-m-d'),
                    'status'        => $w->status,
                    'document_url'  => $w->warranty_document_path ? asset($w->warranty_document_path) : null,
                    'action'        => 'RAISE SERVICE REQUEST',
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => $warranties,
        ]);
    }

    /**
     * POST /api/customer/service-tickets
     * Create service or problem request with automatic DSP allocation based on customer location.
     */
    public function createServiceTicket(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'success' => false, 'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required.'], 401);
        }

        if (!$request->filled('details') && $request->filled('description')) {
            $request->merge(['details' => $request->description]);
        }

        $validator = Validator::make($request->all(), [
            'subject'        => 'required|string|max:255',
            'service_type'   => 'required|string|in:warranty,installation,repair,replacement,technical_support,complaint,maintenance,breakdown,problem,other',
            'priority'       => 'nullable|string|in:low,medium,high,urgent',
            'details'        => 'required|string',
            'booking_id'     => 'nullable|exists:bookings,id',
            'customer_name'  => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:500',
            'pincode'        => 'nullable|string|max:10',
            'city'           => 'nullable|string|max:100',
            'state'          => 'nullable|string|max:100',
            'photo'          => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
            'video'          => 'nullable|file|mimes:mp4,mov,avi|max:51200',
            'invoice'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        try {
            $ticketNumber = 'TKT-' . date('Y') . '-' . rand(10000, 99999);

            // Determine customer location
            $booking = $request->filled('booking_id') ? Booking::find($request->booking_id) : null;
            $pincode = trim((string)($request->pincode ?: ($booking?->pincode ?: $user->pincode)));
            $city = trim((string)($request->city ?: ($booking?->city ?: $user->city)));
            $state = trim((string)($request->state ?: ($booking?->state ?: $user->state)));
            $address = $request->address ?: ($booking?->shipping_address ?: $user->address);
            $customerName = $request->customer_name ?: ($booking?->customer_name ?: $user->name);
            $customerPhone = $request->customer_phone ?: ($booking?->customer_phone ?: $user->phone);

            // Automatically allocate DSP based on customer location
            $dspId = $booking?->dsp_id;
            $allocatedDsp = null;

            if (!empty($dspId)) {
                $allocatedDsp = DspApplication::find($dspId);
            }

            if (!$allocatedDsp) {
                $matchingService = app(DspMatchingService::class);
                $allocatedDsp = $matchingService->getBestMatchingDsp($pincode, $city, $state);
                $dspId = $allocatedDsp?->id;
            }

            // Handle file attachments
            $attachmentPaths = [];
            $destination = public_path('uploads/service_attachments');
            if (!file_exists($destination)) {
                mkdir($destination, 0755, true);
            }

            if ($request->hasFile('photo')) {
                $f = $request->file('photo');
                $name = 'photo_' . time() . '_' . Str::random(6) . '.' . $f->getClientOriginalExtension();
                $f->move($destination, $name);
                $attachmentPaths['photo'] = 'uploads/service_attachments/' . $name;
            }
            if ($request->hasFile('video')) {
                $f = $request->file('video');
                $name = 'video_' . time() . '_' . Str::random(6) . '.' . $f->getClientOriginalExtension();
                $f->move($destination, $name);
                $attachmentPaths['video'] = 'uploads/service_attachments/' . $name;
            }
            if ($request->hasFile('invoice')) {
                $f = $request->file('invoice');
                $name = 'invoice_' . time() . '_' . Str::random(6) . '.' . $f->getClientOriginalExtension();
                $f->move($destination, $name);
                $attachmentPaths['invoice'] = 'uploads/service_attachments/' . $name;
            }

            $detailsText = $request->details;
            if (!empty($attachmentPaths)) {
                $detailsText .= "\n\nAttachments: " . json_encode($attachmentPaths);
            }

            $ticket = ServiceRequest::create([
                'ticket_number'  => $ticketNumber,
                'user_id'        => $user->id,
                'customer_name'  => $customerName,
                'customer_phone' => $customerPhone,
                'address'        => $address,
                'pincode'        => $pincode,
                'city'           => $city,
                'state'          => $state,
                'booking_id'     => $booking?->id,
                'dsp_id'         => $dspId,
                'subject'        => $request->subject,
                'service_type'   => $request->service_type,
                'priority'       => $request->input('priority', 'medium'),
                'status'         => 'open',
                'is_attended'    => false,
                'details'        => $detailsText,
                'attachments'    => $attachmentPaths,
            ]);

            $dspData = null;
            if ($allocatedDsp) {
                $dspData = [
                    'id'            => $allocatedDsp->id,
                    'business_name' => $allocatedDsp->business_name,
                    'contact_name'  => $allocatedDsp->applicant_name,
                    'mobile'        => $allocatedDsp->mobile,
                    'district'      => $allocatedDsp->district,
                    'state'         => $allocatedDsp->state,
                ];
            }

            $allocationMsg = $allocatedDsp
                ? "Allocated to Authorised DSP '{$allocatedDsp->business_name}' for PIN {$pincode}."
                : "Received and will be assigned to your regional Authorised Delivery & Service Partner.";

            return response()->json([
                'status'  => true,
                'success' => true,
                'message' => 'Service request submitted successfully. ' . $allocationMsg,
                'data'    => [
                    'id'            => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'subject'       => $ticket->subject,
                    'service_type'  => $ticket->service_type,
                    'priority'      => $ticket->priority,
                    'status'        => $ticket->status,
                    'is_attended'   => false,
                    'location'      => [
                        'address' => $ticket->address,
                        'pincode' => $ticket->pincode,
                        'city'    => $ticket->city,
                        'state'   => $ticket->state,
                    ],
                    'allocated_dsp' => $dspData,
                    'created_at'    => $ticket->created_at->format('Y-m-d H:i:s'),
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to create service ticket: ' . $e->getMessage(),
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/customer/service-tickets
     * List customer service tickets with allocated DSP and attended status.
     */
    public function listServiceTickets(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required.'], 401);
        }

        $tickets = ServiceRequest::where('user_id', $user->id)
            ->with(['dsp', 'booking.product'])
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($t) {
                return [
                    'id'               => $t->id,
                    'ticket_number'    => $t->ticket_number,
                    'subject'          => $t->subject,
                    'service_type'     => $t->service_type,
                    'priority'         => $t->priority,
                    'status'           => $t->status,
                    'is_attended'      => (bool)$t->is_attended,
                    'attended_at'      => $t->attended_at ? $t->attended_at->format('Y-m-d H:i:s') : null,
                    'attended_by'      => $t->attended_by_name,
                    'details'          => $t->details,
                    'attachments'      => $t->attachments,
                    'dsp_notes'        => $t->dsp_notes,
                    'resolved_at'      => $t->resolved_at ? $t->resolved_at->format('Y-m-d H:i:s') : null,
                    'resolution_notes' => $t->resolution_notes,
                    'allocated_dsp'    => $t->dsp ? [
                        'id'            => $t->dsp->id,
                        'business_name' => $t->dsp->business_name,
                        'contact_name'  => $t->dsp->applicant_name,
                        'mobile'        => $t->dsp->mobile,
                        'district'      => $t->dsp->district,
                        'state'         => $t->dsp->state,
                    ] : null,
                    'created_at'       => $t->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => $tickets,
        ]);
    }

    /**
     * GET /api/customer/service-tickets/{id}
     * Get details of a single service ticket.
     */
    public function showServiceTicket(Request $request, $id)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $t = ServiceRequest::where('user_id', $user->id)
            ->with(['dsp', 'booking.product'])
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', $id);
                } else {
                    $q->where('ticket_number', $id);
                }
            })
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data'   => [
                'id'               => $t->id,
                'ticket_number'    => $t->ticket_number,
                'subject'          => $t->subject,
                'service_type'     => $t->service_type,
                'priority'         => $t->priority,
                'status'           => $t->status,
                'is_attended'      => (bool)$t->is_attended,
                'attended_at'      => $t->attended_at ? $t->attended_at->format('Y-m-d H:i:s') : null,
                'attended_by'      => $t->attended_by_name,
                'attended_by_phone'=> $t->attended_by_phone,
                'details'          => $t->details,
                'attachments'      => $t->attachments,
                'dsp_notes'        => $t->dsp_notes,
                'resolved_at'      => $t->resolved_at ? $t->resolved_at->format('Y-m-d H:i:s') : null,
                'resolution_notes' => $t->resolution_notes,
                'allocated_dsp'    => $t->dsp ? [
                    'id'            => $t->dsp->id,
                    'business_name' => $t->dsp->business_name,
                    'contact_name'  => $t->dsp->applicant_name,
                    'mobile'        => $t->dsp->mobile,
                    'district'      => $t->dsp->district,
                    'state'         => $t->dsp->state,
                ] : null,
                'created_at'       => $t->created_at->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * GET /api/customer/dsp/lookup
     * Lookup available DSP for a given pincode/city.
     */
    public function lookupLocalDsp(Request $request)
    {
        $pincode = $request->input('pincode');
        $district = $request->input('city') ?? $request->input('district');
        $state = $request->input('state');

        $matchingService = app(DspMatchingService::class);
        $dsps = $matchingService->findAvailableDsps($pincode, $district, $state);

        return response()->json([
            'status' => true,
            'count'  => $dsps->count(),
            'data'   => $dsps->map(function ($d) {
                return [
                    'id'            => $d->id,
                    'business_name' => $d->business_name,
                    'contact_name'  => $d->applicant_name,
                    'mobile'        => $d->mobile,
                    'district'      => $d->district,
                    'state'         => $d->state,
                    'match_type'    => $d->match_type ?? 'local',
                    'match_label'   => $d->match_label ?? 'Authorised Partner',
                ];
            }),
        ]);
    }

    /**
     * POST /api/customer/installations/schedule
     * Schedule, reschedule, or rate installation.
     */
    public function scheduleInstallation(Request $request)
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated. Authorization token (Bearer <api_token>) is required.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'booking_id'   => 'required|exists:bookings,id',
            'scheduled_at' => 'required|date',
            'rating'       => 'nullable|integer|min:1|max:5',
            'feedback'     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        $installation = Installation::firstOrCreate(
            ['booking_id' => $request->booking_id, 'user_id' => $user->id],
            ['status' => 'scheduled', 'technician_name' => 'Assigned Technician', 'technician_phone' => '+91-9876543210']
        );

        $installation->scheduled_at = $request->scheduled_at;
        if ($request->has('rating')) {
            $installation->rating   = $request->rating;
            $installation->feedback = $request->feedback;
            $installation->status   = 'completed';
        } else {
            $installation->status   = 'scheduled';
        }
        $installation->save();

        return response()->json([
            'status'  => true,
            'message' => 'Installation schedule updated.',
            'data'    => [
                'id'               => $installation->id,
                'technician_name'  => $installation->technician_name,
                'technician_phone' => $installation->technician_phone,
                'scheduled_at'     => $installation->scheduled_at ? $installation->scheduled_at->format('Y-m-d H:i:s') : null,
                'status'           => $installation->status,
                'rating'           => $installation->rating,
            ],
        ]);
    }
}
