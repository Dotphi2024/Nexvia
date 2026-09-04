<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use App\Models\ServiceRequest;
use App\Models\Installation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WarrantyAndServiceApiController extends Controller
{
    /**
     * GET /api/customer/warranties
     * List automatic warranties registered for customer.
     */
    public function warranties(Request $request)
    {
        $user = $request->user('customer');
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
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
     * Create service ticket with attachments.
     */
    public function createServiceTicket(Request $request)
    {
        $user = $request->user('customer');
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'subject'      => 'required|string|max:255',
            'service_type' => 'required|string|in:warranty,installation,repair,replacement,technical_support,complaint',
            'details'      => 'required|string',
            'booking_id'   => 'nullable|exists:bookings,id',
            'photo'        => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
            'video'        => 'nullable|file|mimes:mp4,mov,avi|max:51200',
            'invoice'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'Validation error', 'errors' => $validator->errors()], 422);
        }

        try {
            $ticketNumber = 'TKT-' . date('Y') . '-' . rand(10000, 99999);

            $attachmentPaths = [];
            if ($request->hasFile('photo')) {
                $attachmentPaths['photo'] = $request->file('photo')->store('service_attachments', 'public');
            }
            if ($request->hasFile('video')) {
                $attachmentPaths['video'] = $request->file('video')->store('service_attachments', 'public');
            }
            if ($request->hasFile('invoice')) {
                $attachmentPaths['invoice'] = $request->file('invoice')->store('service_attachments', 'public');
            }

            $detailsText = $request->details;
            if (!empty($attachmentPaths)) {
                $detailsText .= "\n\nAttachments: " . json_encode($attachmentPaths);
            }

            $ticket = ServiceRequest::create([
                'ticket_number' => $ticketNumber,
                'user_id'       => $user->id,
                'booking_id'    => $request->booking_id,
                'subject'       => $request->subject,
                'service_type'  => $request->service_type,
                'status'        => 'open',
                'details'       => $detailsText,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Service ticket submitted successfully.',
                'data'    => [
                    'id'            => $ticket->id,
                    'ticket_number' => $ticket->ticket_number,
                    'subject'       => $ticket->subject,
                    'service_type'  => $ticket->service_type,
                    'status'        => $ticket->status,
                    'created_at'    => $ticket->created_at->format('Y-m-d H:i:s'),
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to create service ticket.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/customer/service-tickets
     * List customer service tickets.
     */
    public function listServiceTickets(Request $request)
    {
        $user = $request->user('customer');
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
        }

        $tickets = ServiceRequest::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($t) {
                return [
                    'id'            => $t->id,
                    'ticket_number' => $t->ticket_number,
                    'subject'       => $t->subject,
                    'service_type'  => $t->service_type,
                    'status'        => $t->status,
                    'details'       => $t->details,
                    'created_at'    => $t->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => $tickets,
        ]);
    }

    /**
     * POST /api/customer/installations/schedule
     * Schedule, reschedule, or rate installation.
     */
    public function scheduleInstallation(Request $request)
    {
        $user = $request->user('customer');
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Unauthenticated.'], 401);
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
