<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\UserAddress;
use Illuminate\Http\Request;

class CustomerAdminController extends Controller
{
    /**
     * Display listing of all customer accounts
     */
    public function index(Request $request)
    {
        $query = Customer::withCount(['addresses', 'bookings']);

        // Search by Name, Email, or Phone
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total'          => Customer::count(),
            'active'         => Customer::where('status', 'active')->count(),
            'inactive'       => Customer::where('status', 'inactive')->count(),
            'totalAddresses' => UserAddress::count(),
        ];

        return view('admin.customers.index', compact('customers', 'stats'));
    }

    /**
     * Display detailed profile & addresses of a customer
     */
    public function show($id)
    {
        $customer = Customer::with(['addresses', 'bookings'])->findOrFail($id);

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Toggle Customer Active / Inactive Status
     */
    public function updateStatus(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $newStatus = $customer->status === 'active' ? 'inactive' : 'active';
        $customer->update(['status' => $newStatus]);

        return redirect()->back()->with('success', "Customer status updated to {$newStatus}.");
    }

    /**
     * Delete Customer Account
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Customer account deleted successfully.');
    }
}
