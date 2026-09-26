<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageApiController extends Controller
{
    /**
     * Format a page model into standardized API response structure.
     */
    protected function formatPage(Page $page): array
    {
        return [
            'id'               => $page->id,
            'title'            => $page->title,
            'slug'             => $page->slug,
            'excerpt'          => $page->excerpt,
            'content'          => $page->content,
            'points'           => $page->points ?? [],
            'meta_title'       => $page->meta_title,
            'meta_description' => $page->meta_description,
            'meta_keywords'    => $page->meta_keywords,
            'sort_order'       => (int) $page->sort_order,
            'updated_at'       => $page->updated_at ? $page->updated_at->toIso8601String() : null,
            'created_at'       => $page->created_at ? $page->created_at->toIso8601String() : null,
        ];
    }

    /**
     * GET /api/privacy-policy or /api/customer/privacy-policy
     * Fetch the active Privacy Policy with full content and key points.
     */
    public function privacyPolicy(Request $request)
    {
        try {
            $page = Page::active()
                ->where(function ($q) {
                    $q->where('slug', 'privacy-policy')
                      ->orWhere('slug', 'privacy')
                      ->orWhere('title', 'LIKE', '%Privacy Policy%');
                })
                ->first();

            if (!$page) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Privacy policy page not found or is currently inactive.',
                ], 404);
            }

            return response()->json([
                'status'  => true,
                'message' => 'Privacy policy retrieved successfully.',
                'data'    => $this->formatPage($page),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve privacy policy.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/pages
     * List all active CMS and policy pages.
     */
    public function index(Request $request)
    {
        try {
            $pages = Page::active()
                ->ordered()
                ->get()
                ->map(function ($page) {
                    $points = $page->points ?? [];
                    return [
                        'id'           => $page->id,
                        'title'        => $page->title,
                        'slug'         => $page->slug,
                        'excerpt'      => $page->excerpt,
                        'points_count' => is_array($points) ? count($points) : 0,
                        'points'       => $points,
                        'sort_order'   => (int) $page->sort_order,
                        'updated_at'   => $page->updated_at ? $page->updated_at->toIso8601String() : null,
                    ];
                });

            return response()->json([
                'status'  => true,
                'message' => 'Pages list retrieved successfully.',
                'total'   => $pages->count(),
                'data'    => $pages,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve pages list.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/pages/{slugOrId}
     * Fetch a specific page by slug or ID with full content and structured points.
     */
    public function show(Request $request, $slugOrId)
    {
        try {
            $query = Page::active();

            if (is_numeric($slugOrId)) {
                $page = $query->where('id', $slugOrId)->first();
            } else {
                $page = $query->where('slug', strtolower(trim($slugOrId)))->first();
            }

            if (!$page) {
                return response()->json([
                    'status'  => false,
                    'message' => "Page '{$slugOrId}' not found or is currently inactive.",
                ], 404);
            }

            return response()->json([
                'status'  => true,
                'message' => "{$page->title} retrieved successfully.",
                'data'    => $this->formatPage($page),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve page details.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/terms-and-conditions
     */
    public function termsAndConditions(Request $request)
    {
        return $this->show($request, 'terms-and-conditions');
    }

    /**
     * GET /api/refund-policy
     */
    public function refundPolicy(Request $request)
    {
        return $this->show($request, 'refund-policy');
    }

    /**
     * GET /api/about-us
     */
    public function aboutUs(Request $request)
    {
        return $this->show($request, 'about-us');
    }

    /**
     * GET or POST /api/contact-us
     */
    public function contactUs(Request $request)
    {
        if ($request->isMethod('post') && ($request->filled('message') || $request->filled('email') || $request->filled('name'))) {
            $name    = trim($request->input('name', 'NEXVIA Visitor'));
            $email   = trim($request->input('email', ''));
            $phone   = trim($request->input('phone', 'N/A'));
            $subject = trim($request->input('subject', 'General Inquiry'));
            $message = trim($request->input('message', ''));

            $adminEmail = config('mail.from.address', 'nexviadls@gmail.com');
            $appName    = config('mail.from.name', 'NEXVIA');

            // Send notification to admin
            try {
                \Illuminate\Support\Facades\Mail::raw("New Contact Us Inquiry Received!\n\nName: {$name}\nEmail: {$email}\nPhone: {$phone}\nSubject: {$subject}\n\nMessage:\n{$message}\n\nSubmitted at: " . now()->toDateTimeString(), function ($m) use ($adminEmail, $name, $subject, $email) {
                    $m->to($adminEmail)
                      ->subject("Contact Us Inquiry: {$subject} - {$name}");
                    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $m->replyTo($email, $name);
                    }
                });
            } catch (\Throwable $e) {
                \Log::warning("Contact Us admin email failed: " . $e->getMessage());
            }

            // Send auto-acknowledgement to sender if email provided
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                try {
                    \Illuminate\Support\Facades\Mail::raw("Dear {$name},\n\nThank you for contacting {$appName}!\n\nWe have received your message regarding \"{$subject}\" and our team will get back to you shortly.\n\nYour message:\n{$message}\n\nBest regards,\n{$appName} Support Team", function ($m) use ($email, $appName, $subject) {
                        $m->to($email)
                          ->subject("Thank you for contacting {$appName} - {$subject}");
                    });
                } catch (\Throwable $e) {
                    \Log::warning("Contact Us auto-reply email failed: " . $e->getMessage());
                }
            }

            return response()->json([
                'status'  => true,
                'message' => 'Your message has been sent successfully. We will get back to you shortly!',
            ], 200);
        }

        return $this->show($request, 'contact-us');
    }
}
