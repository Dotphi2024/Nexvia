<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PopupLead;
use App\Models\PopupSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PopupLeadApiController extends Controller
{
    /**
     * Fetch active popup configuration for frontend website
     */
    public function settings(): JsonResponse
    {
        $settings = PopupSetting::getSettings();

        return response()->json([
            'status'  => true,
            'message' => 'Popup configuration retrieved successfully',
            'data'    => [
                'is_enabled'      => (bool) $settings->is_enabled,
                'delay_seconds'   => (int) ($settings->delay_seconds ?? 7), // 5-10s default
                'title'           => $settings->title ?: 'Get Exclusive Offers & Instant Callback',
                'subtitle'        => $settings->subtitle ?: 'Enter your details below and our product specialist will connect with you within minutes.',
                'button_text'     => $settings->button_text ?: 'Request Callback Now',
                'success_message' => $settings->success_message ?: 'Thank you! We have received your request and will call you shortly.',
                'reappear_hours'  => (int) ($settings->reappear_hours ?? 24),
            ],
        ], 200);
    }

    /**
     * Submit popup lead from frontend website
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'       => 'required|string|min:2|max:100',
            'mobile'     => ['required', 'string', 'regex:/^(\+91[\-\s]?)?[0]?[6789]\d{9}$/'],
            'email'      => 'nullable|email|max:150',
            'notes'      => 'nullable|string|max:1000',
            'source_url' => 'nullable|string|max:500',
        ], [
            'name.required'   => 'Please enter your name.',
            'name.min'        => 'Name must be at least 2 characters.',
            'mobile.required' => 'Please enter your mobile number.',
            'mobile.regex'    => 'Please enter a valid 10-digit mobile number.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Clean mobile number (strip non-digits, keep last 10 digits or clean +91 format)
        $cleanMobile = preg_replace('/[^0-9]/', '', $request->input('mobile'));
        if (strlen($cleanMobile) > 10 && str_starts_with($cleanMobile, '91')) {
            $cleanMobile = substr($cleanMobile, 2);
        }

        $sourceUrl = $request->input('source_url') ?? $request->header('Referer') ?? $request->fullUrl();

        $lead = PopupLead::create([
            'name'         => trim($request->input('name')),
            'mobile'       => $cleanMobile,
            'email'        => $request->input('email') ? trim($request->input('email')) : null,
            'notes'        => $request->input('notes') ? trim($request->input('notes')) : null,
            'source_url'   => $sourceUrl,
            'ip_address'   => $request->ip(),
            'user_agent'   => substr((string) $request->header('User-Agent'), 0, 500),
            'status'       => 'pending',
            'admin_notes'  => null,
            'contacted_at' => null,
        ]);

        $settings = PopupSetting::getSettings();
        $successMsg = $settings->success_message ?: 'Thank you! We have received your request. Our specialist will call you shortly.';

        return response()->json([
            'status'  => true,
            'message' => $successMsg,
            'data'    => [
                'id'         => $lead->id,
                'name'       => $lead->name,
                'mobile'     => $lead->mobile,
                'status'     => $lead->status,
                'created_at' => $lead->created_at?->format('Y-m-d H:i:s'),
            ],
        ], 201);
    }
}
