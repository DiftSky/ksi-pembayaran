<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the invoices.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        $query = Invoice::with(['customer', 'merchant']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by merchant
        if ($request->has('merchant_id')) {
            $query->where('merchant_id', $request->merchant_id);
        }

        // Filter by customer
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by due date range
        if ($request->has('due_date_from') && $request->has('due_date_to')) {
            $query->whereBetween('due_date', [$request->due_date_from, $request->due_date_to]);
        } elseif ($request->has('due_date_from')) {
            $query->where('due_date', '>=', $request->due_date_from);
        } elseif ($request->has('due_date_to')) {
            $query->where('due_date', '<=', $request->due_date_to);
        }

        $invoices = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $invoices
        ]);
    }

    /**
     * Store a newly created invoice in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', Invoice::class);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'merchant_id' => 'required|exists:merchants,id',
            'amount' => 'required|numeric|min:1',
            'tax_amount' => 'nullable|numeric|min:0',
            'due_date' => 'required|date|after_or_equal:today',
            'description' => 'nullable|string',
            'status' => 'nullable|in:draft,pending,paid,overdue,cancelled',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total amount
            $amount = $validated['amount'];
            $taxAmount = $validated['tax_amount'] ?? 0;
            $totalAmount = $amount + $taxAmount;

            // Generate invoice number
            $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT);

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $validated['customer_id'],
                'merchant_id' => $validated['merchant_id'],
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'due_date' => $validated['due_date'],
                'status' => $validated['status'] ?? 'pending',
                'description' => $validated['description'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'data' => $invoice->fresh(['customer', 'merchant'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified invoice.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $invoice = Invoice::with(['customer', 'merchant', 'payments'])->findOrFail($id);
        
        $this->authorize('view', $invoice);

        return response()->json([
            'success' => true,
            'data' => $invoice
        ]);
    }

    /**
     * Update the specified invoice in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        
        $this->authorize('update', $invoice);

        // Don't allow updating if invoice is already paid
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update a paid invoice'
            ], 422);
        }

        $validated = $request->validate([
            'customer_id' => 'sometimes|exists:customers,id',
            'merchant_id' => 'sometimes|exists:merchants,id',
            'amount' => 'sometimes|numeric|min:1',
            'tax_amount' => 'nullable|numeric|min:0',
            'due_date' => 'sometimes|date',
            'description' => 'nullable|string',
            'status' => 'nullable|in:draft,pending,overdue,cancelled',
        ]);

        try {
            DB::beginTransaction();

            // Update invoice fields
            $invoice->fill($validated);

            // Recalculate total amount if amount or tax_amount was changed
            if (isset($validated['amount']) || isset($validated['tax_amount'])) {
                $amount = $validated['amount'] ?? $invoice->amount;
                $taxAmount = $validated['tax_amount'] ?? $invoice->tax_amount;
                $invoice->total_amount = $amount + $taxAmount;
            }

            $invoice->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice updated successfully',
                'data' => $invoice->fresh(['customer', 'merchant'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified invoice from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        
        $this->authorize('delete', $invoice);

        // Don't allow deleting if invoice is not in draft status
        if ($invoice->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft invoices can be deleted'
            ], 422);
        }

        try {
            $invoice->delete();

            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
