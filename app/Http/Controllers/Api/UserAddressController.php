<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserAddressController extends Controller
{
    /**
     * GET /api/user/addresses
     * Fetch user saved delivery addresses
     */
    public function index(Request $request)
    {
        try {
            $customer = $request->get('authenticated_customer') ?? $request->user();

            if (!$customer) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $addresses = UserAddress::where('user_id', $customer->id)
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status'  => true,
                'message' => 'Addresses retrieved successfully.',
                'data'    => $addresses,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch addresses.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * POST /api/user/addresses
     * Save new shipping address
     * Params: { name, phone, street, city, state, pincode, isDefault }
     */
    public function store(Request $request)
    {
        $customer = $request->get('authenticated_customer') ?? $request->user();

        if (!$customer) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|max:20',
            'street'    => 'required|string',
            'city'      => 'required|string|max:100',
            'state'     => 'required|string|max:100',
            'pincode'   => 'required|string|max:20',
            'isDefault' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $isDefault = (bool)($request->input('isDefault') ?? $request->input('is_default') ?? false);
            $addressId = $request->input('address_id') ?? $request->input('addressId');

            // If address is set to default, reset other addresses for this user
            if ($isDefault) {
                UserAddress::where('user_id', $customer->id)->update(['is_default' => false]);
            } else {
                // If it's the user's first address, auto-set default
                $count = UserAddress::where('user_id', $customer->id)->count();
                if ($count === 0) {
                    $isDefault = true;
                }
            }

            // Update existing address ONLY if address_id / addressId is explicitly passed
            if ($addressId) {
                $address = UserAddress::where('id', $addressId)
                    ->where('user_id', $customer->id)
                    ->first();

                if ($address) {
                    $address->update([
                        'name'       => trim($request->name),
                        'phone'      => trim($request->phone),
                        'street'     => trim($request->street),
                        'city'       => trim($request->city),
                        'state'      => trim($request->state),
                        'pincode'    => trim($request->pincode),
                        'is_default' => $isDefault,
                    ]);

                    return response()->json([
                        'status'  => true,
                        'message' => 'Address updated successfully.',
                        'data'    => $address->fresh(),
                    ], 200);
                }
            }

            $address = UserAddress::create([
                'user_id'    => $customer->id,
                'name'       => trim($request->name),
                'phone'      => trim($request->phone),
                'street'     => trim($request->street),
                'city'       => trim($request->city),
                'state'      => trim($request->state),
                'pincode'    => trim($request->pincode),
                'is_default' => $isDefault,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Address saved successfully.',
                'data'    => $address,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to save address.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * DELETE /api/user/addresses/{id}
     * Remove saved address
     */
    public function destroy(Request $request, $id = null)
    {
        $customer = $request->get('authenticated_customer') ?? $request->user();

        if (!$customer) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $addressId = $id
            ?? $request->input('address_id')
            ?? $request->input('addressId')
            ?? $request->input('id');

        if (!$addressId) {
            return response()->json([
                'status'  => false,
                'message' => 'Address ID (address_id) is required to delete.',
            ], 422);
        }

        try {
            $address = UserAddress::where('id', $addressId)
                ->where('user_id', $customer->id)
                ->first();

            if (!$address) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Address not found or does not belong to you.',
                ], 404);
            }

            $wasDefault = $address->is_default;
            $address->delete();

            // If default was deleted, set next available address as default
            if ($wasDefault) {
                $next = UserAddress::where('user_id', $customer->id)->first();
                if ($next) {
                    $next->update(['is_default' => true]);
                }
            }

            return response()->json([
                'status'  => true,
                'message' => 'Address removed successfully.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete address.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
