<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MerchantController extends Controller
{
    /**
     * Display a listing of the merchants.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Merchant::class);

        $query = Merchant::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $merchants = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $merchants
        ]);
    }

    /**
     * Store a newly created merchant in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', Merchant::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:merchants,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'business_type' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,inactive,pending,suspended',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $merchant = Merchant::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Merchant created successfully',
                'data' => $merchant
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create merchant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified merchant.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $merchant = Merchant::with(['invoices', 'payments'])->findOrFail($id);
        
        $this->authorize('view', $merchant);

        return response()->json([
            'success' => true,
            'data' => $merchant
        ]);
    }

    /**
     * Update the specified merchant in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $merchant = Merchant::findOrFail($id);
        
        $this->authorize('update', $merchant);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:merchants,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'business_type' => 'nullable|string|max:100',
            'bank_account' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,inactive,pending,suspended',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $merchant->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Merchant updated successfully',
                'data' => $merchant->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update merchant',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified merchant from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $merchant = Merchant::findOrFail($id);
        
        $this->authorize('delete', $merchant);

        // Check if merchant has any invoices or payments
        if ($merchant->invoices()->count() > 0 || $merchant->payments()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete merchant with existing invoices or payments'
            ], 422);
        }

        try {
            $merchant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Merchant deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete merchant',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
