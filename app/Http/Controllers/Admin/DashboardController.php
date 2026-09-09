<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTransfer;
use App\Models\Category;
use App\Models\Customer;
use App\Models\FraudFlag;
use App\Models\Product;
use App\Models\SelfDealerWallet;
use App\Models\ServiceRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();

        $revenue             = Booking::sum('booking_amount') + Booking::where('payment_status', 'fully_paid')->sum('balance_amount');
        $todaysOrders        = Booking::whereDate('created_at', $today)->count();
        $todaysBookings      = Booking::whereDate('created_at', $today)->where('payment_type', 'booking_20')->count();
        $collections         = $revenue;
        $outstandingBalance  = Booking::where('payment_status', 'paid')->sum('balance_amount');
        $totalCustomers      = Customer::count();
        $inventoryCount      = Product::sum('stock');
        $serviceRequestsCount = ServiceRequest::where('status', 'open')->count();

        // Self Dealer real stats
        $selfDealers            = Customer::where('is_self_dealer', true)->where('self_dealer_status', 'active')->count();
        $totalIncentivePoints   = SelfDealerWallet::sum('total_earned');
        $pendingPoints          = SelfDealerWallet::sum('pending_points');
        $availablePoints        = SelfDealerWallet::sum('available_points');
        $redeemedPoints         = SelfDealerWallet::sum('redeemed_points');
        $productCreditLiability = $availablePoints + $pendingPoints;
        $fraudFlagsCount        = FraudFlag::where('status', 'pending_review')->count();

        $recentBookings  = Booking::with('product')->latest()->take(6)->get();
        $recentTransfers = BookingTransfer::with(['booking', 'fromUser'])->latest()->take(5)->get();

        return view('admin.dashboard.index', compact(
            'revenue',
            'todaysOrders',
            'todaysBookings',
            'collections',
            'outstandingBalance',
            'totalCustomers',
            'selfDealers',
            'productCreditLiability',
            'inventoryCount',
            'serviceRequestsCount',
            'recentBookings',
            'recentTransfers',
            'totalIncentivePoints',
            'pendingPoints',
            'availablePoints',
            'redeemedPoints',
            'fraudFlagsCount'
        ));
    }
}

