<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerCategoryProgress;
use App\Models\ReferralStageConfig;
use Illuminate\Http\Request;

class CategoryApiController extends Controller
{
    /**
     * Resolve authenticated customer whether from middleware, guard, bearer token, or headers.
     */
    protected function getAuthenticatedCustomer(Request $request)
    {
        $customer = $request->get('authenticated_customer');
        if ($customer) {
            return $customer;
        }

        try {
            if (auth()->guard('customer')->check()) {
                return auth()->guard('customer')->user();
            }
        } catch (\Throwable $e) {
            // Guard fallback
        }

        $token = $request->bearerToken()
            ?? $request->input('token')
            ?? $request->header('token');

        $userId = $request->header('User-Id')
            ?? $request->header('id')
            ?? $request->input('user_id')
            ?? $request->input('id');

        if (!empty($token)) {
            $found = Customer::where('api_token', $token)->first();
            if ($found) return $found;
        }

        if (!empty($userId) && is_numeric($userId)) {
            $found = Customer::find($userId);
            if ($found) return $found;
        }

        try {
            return $request->user();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * GET/POST /api/categories or /api/customer/categories
     * Get category list with product counts, referral codes, and reward slabs.
     */
    public function index(Request $request)
    {
        try {
            $user = $this->getAuthenticatedCustomer($request);

            $progressRecords = $user
                ? CustomerCategoryProgress::where('user_id', $user->id)->get()->keyBy('category_id')
                : collect();

            $stageConfigs = ReferralStageConfig::orderBy('stage_number')->get()->keyBy('stage_number');
            $defaultRates = [1 => 10.00, 2 => 12.00, 3 => 15.00, 4 => 18.00, 5 => 20.00];

            $query = Category::withCount(['products' => function ($q) {
                $q->where('status', 'active');
            }])->where('is_active', true);

            // Optional search filter
            if ($search = $request->input('search')) {
                $search = trim($search);
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('referral_category_code', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            // Optional referral eligibility filter
            if ($request->has('referral_eligible')) {
                $query->where('referral_eligible', filter_var($request->input('referral_eligible'), FILTER_VALIDATE_BOOLEAN));
            }

            $categories = $query->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc')
                ->get()
                ->map(function ($cat) use ($progressRecords, $user, $stageConfigs, $defaultRates) {
                    $imageUrl = \App\Helpers\ImageHelper::resolve($cat->image);

                    $catCode = $cat->referral_category_code ?: strtoupper(substr($cat->slug, 0, 3));

                    $userProg = $progressRecords->get($cat->id);
                    $currentStage = $userProg ? (int) $userProg->current_stage : 1;
                    $currentRate  = isset($stageConfigs[$currentStage])
                        ? (float) $stageConfigs[$currentStage]->incentive_percentage
                        : ($defaultRates[$currentStage] ?? 10.00);

                    $nextStageNumber = ($currentStage >= 5) ? 1 : ($currentStage + 1);
                    $nextRate = isset($stageConfigs[$nextStageNumber])
                        ? (float) $stageConfigs[$nextStageNumber]->incentive_percentage
                        : ($defaultRates[$nextStageNumber] ?? 10.00);

                    $cycleNumber  = $userProg ? (int) $userProg->cycle_number : 1;
                    $referralsInCycle = $userProg ? (int) $userProg->referral_count : 0;
                    $totalReferrals = $userProg ? (int) $userProg->total_referrals_all_time : 0;

                    // 5-stage progression breakdown
                    $stagesInfo = [];
                    for ($s = 1; $s <= 5; $s++) {
                        $rate = isset($stageConfigs[$s]) ? (float) $stageConfigs[$s]->incentive_percentage : $defaultRates[$s];
                        $stagesInfo[] = [
                            'stage'             => $s,
                            'reward_percentage' => $rate,
                            'is_current'        => ($s === $currentStage),
                            'is_completed'      => ($s < $currentStage),
                            'label'             => "Stage {$s}: {$rate}% Product Credit",
                        ];
                    }

                    return [
                        'id'                     => $cat->id,
                        'name'                   => $cat->name,
                        'slug'                   => $cat->slug,
                        'type'                   => $cat->type,
                        // Referral Code & Identification
                        'referral_category_code' => $catCode,
                        'category_code'          => $catCode,      // Short alias
                        'referral_code'          => $catCode,      // Generic alias for clients looking for referral_code
                        'referral_eligible'      => (bool) $cat->referral_eligible,
                        'commission_percentage'  => (float) $cat->commission_percentage,
                        // Referral Reward Slabs (Variant A: 10% → 12% → 15% → 18% → 20%)
                        'reward_slab'            => '10% → 20% Product Credit',
                        'starting_reward_pct'    => 10.00,
                        'max_reward_pct'         => 20.00,
                        'stages_cycle'           => [10.00, 12.00, 15.00, 18.00, 20.00],
                        'stages_info'            => $stagesInfo,
                        // User-specific Referral Progress (if logged in)
                        'user_progress'          => $user ? [
                            'customer_referral_code'   => $user->referral_code,
                            'referral_share_code'      => $user->referral_code,
                            'current_stage'            => $currentStage,
                            'current_incentive_pct'    => $currentRate,
                            'next_stage'               => $nextStageNumber,
                            'next_incentive_pct'       => $nextRate,
                            'cycle_number'             => $cycleNumber,
                            'referrals_in_cycle'       => $referralsInCycle,
                            'total_referrals_all_time' => $totalReferrals,
                            'is_final_stage'           => ($currentStage === 5),
                            'referral_share_link'      => url('/register?ref=' . $user->referral_code . '&cat=' . $catCode),
                            'referral_share_message'   => "Shop {$cat->name} on NEXVIA and get exclusive benefits using my referral code: {$user->referral_code}",
                        ] : null,
                        'description'            => $cat->description,
                        'image'                  => $imageUrl,
                        'imageUrl'               => $imageUrl,
                        'icon'                   => $cat->icon,
                        'products_count'         => $cat->products_count,
                    ];
                });

            return response()->json([
                'status'  => true,
                'message' => 'Categories retrieved successfully.',
                'total'   => $categories->count(),
                'data'    => $categories,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve categories.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * GET /api/categories/{idOrSlug} or /api/customer/categories/{idOrSlug}
     * Get single category detail with products and referral slabs.
     */
    public function show(Request $request, $idOrSlug)
    {
        try {
            $user = $this->getAuthenticatedCustomer($request);

            $category = Category::withCount(['products' => function ($q) {
                $q->where('status', 'active');
            }])
            ->with(['products' => function ($q) {
                $q->where('status', 'active')->limit(12);
            }])
            ->where('is_active', true)
            ->where(function ($q) use ($idOrSlug) {
                if (is_numeric($idOrSlug)) {
                    $q->where('id', $idOrSlug);
                } else {
                    $q->where('slug', $idOrSlug)
                      ->orWhere('referral_category_code', strtoupper($idOrSlug));
                }
            })
            ->first();

            if (!$category) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Category not found.',
                ], 404);
            }

            $userProg = $user
                ? CustomerCategoryProgress::where('user_id', $user->id)->where('category_id', $category->id)->first()
                : null;

            $stageConfigs = ReferralStageConfig::orderBy('stage_number')->get()->keyBy('stage_number');
            $defaultRates = [1 => 10.00, 2 => 12.00, 3 => 15.00, 4 => 18.00, 5 => 20.00];

            $catCode = $category->referral_category_code ?: strtoupper(substr($category->slug, 0, 3));
            $currentStage = $userProg ? (int) $userProg->current_stage : 1;
            $currentRate  = isset($stageConfigs[$currentStage])
                ? (float) $stageConfigs[$currentStage]->incentive_percentage
                : ($defaultRates[$currentStage] ?? 10.00);

            $nextStageNumber = ($currentStage >= 5) ? 1 : ($currentStage + 1);
            $nextRate = isset($stageConfigs[$nextStageNumber])
                ? (float) $stageConfigs[$nextStageNumber]->incentive_percentage
                : ($defaultRates[$nextStageNumber] ?? 10.00);

            $cycleNumber  = $userProg ? (int) $userProg->cycle_number : 1;
            $referralsInCycle = $userProg ? (int) $userProg->referral_count : 0;
            $totalReferrals = $userProg ? (int) $userProg->total_referrals_all_time : 0;

            $imageUrl = \App\Helpers\ImageHelper::resolve($category->image);

            $stagesInfo = [];
            for ($s = 1; $s <= 5; $s++) {
                $rate = isset($stageConfigs[$s]) ? (float) $stageConfigs[$s]->incentive_percentage : $defaultRates[$s];
                $stagesInfo[] = [
                    'stage'             => $s,
                    'reward_percentage' => $rate,
                    'is_current'        => ($s === $currentStage),
                    'is_completed'      => ($s < $currentStage),
                    'label'             => "Stage {$s}: {$rate}% Product Credit",
                ];
            }

            $formattedProducts = $category->products->map(function ($p) use ($catCode) {
                $prodImage = $p->main_image
                    ? (str_starts_with($p->main_image, 'http') ? $p->main_image : asset($p->main_image))
                    : null;
                $eligibleVal = (float) ($p->eligible_referral_value ?: $p->mrp);
                return [
                    'id'                      => $p->id,
                    'name'                    => $p->name,
                    'slug'                    => $p->slug,
                    'model_code'              => $p->model_code,
                    'mrp'                     => (float) $p->mrp,
                    'booking_amount'          => (float) $p->booking_amount,
                    'eligible_referral_value' => $eligibleVal,
                    'referral_eligible'       => (bool) $p->referral_eligible,
                    'self_dealer_eligible'    => (bool) $p->self_dealer_eligible,
                    'main_image'              => $prodImage,
                    'category_code'           => $catCode,
                    'referral_category_code'  => $catCode,
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'Category retrieved successfully.',
                'data'    => [
                    'id'                     => $category->id,
                    'name'                   => $category->name,
                    'slug'                   => $category->slug,
                    'type'                   => $category->type,
                    'referral_category_code' => $catCode,
                    'category_code'          => $catCode,
                    'referral_code'          => $catCode,
                    'referral_eligible'      => (bool) $category->referral_eligible,
                    'commission_percentage'  => (float) $category->commission_percentage,
                    'reward_slab'            => '10% → 20% Product Credit',
                    'starting_reward_pct'    => 10.00,
                    'max_reward_pct'         => 20.00,
                    'stages_cycle'           => [10.00, 12.00, 15.00, 18.00, 20.00],
                    'stages_info'            => $stagesInfo,
                    'user_progress'          => $user ? [
                        'customer_referral_code'   => $user->referral_code,
                        'referral_share_code'      => $user->referral_code,
                        'current_stage'            => $currentStage,
                        'current_incentive_pct'    => $currentRate,
                        'next_stage'               => $nextStageNumber,
                        'next_incentive_pct'       => $nextRate,
                        'cycle_number'             => $cycleNumber,
                        'referrals_in_cycle'       => $referralsInCycle,
                        'total_referrals_all_time' => $totalReferrals,
                        'is_final_stage'           => ($currentStage === 5),
                        'referral_share_link'      => url('/register?ref=' . $user->referral_code . '&cat=' . $catCode),
                        'referral_share_message'   => "Shop {$category->name} on NEXVIA and get exclusive benefits using my referral code: {$user->referral_code}",
                    ] : null,
                    'description'            => $category->description,
                    'image'                  => $imageUrl,
                    'imageUrl'               => $imageUrl,
                    'icon'                   => $category->icon,
                    'products_count'         => $category->products_count,
                    'products'               => $formattedProducts,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to retrieve category.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
