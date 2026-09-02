<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTransfer;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ServiceRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();

        $revenue = Booking::sum('booking_amount') + Booking::where('payment_status', 'fully_paid')->sum('balance_amount');
        $todaysOrders = Booking::whereDate('created_at', $today)->count();
        $todaysBookings = Booking::whereDate('created_at', $today)->where('payment_type', 'booking_20')->count();
        $collections = $revenue;
        $outstandingBalance = Booking::where('payment_status', 'paid')->sum('balance_amount');
        $totalCustomers = Customer::count();
        $selfDealers = 0; // Self Dealer module placeholder
        $productCreditLiability = Customer::sum('wallet_balance');
        $inventoryCount = Product::sum('stock');
        $serviceRequestsCount = ServiceRequest::where('status', 'open')->count();

        $recentBookings = Booking::with('product')->latest()->take(6)->get();
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
            'recentTransfers'
        ));
    }
}
