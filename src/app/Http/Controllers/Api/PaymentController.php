<?php

namespace App\Http\Controllers\Api;

use App\Helpers\EncryptionHelper;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /**
     * Display a listing of the payments.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Payment::class);

        $query = Payment::with(['invoice', 'merchant', 'paymentMethod']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by merchant if provided
        if ($request->has('merchant_id')) {
            $query->where('merchant_id', $request->merchant_id);
        }

        $payments = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Store a newly created payment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->authorize('create', Payment::class);

        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'method_id' => 'required|exists:payment_methods,id',
            'amount' => 'required|numeric|min:1',
            'account_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'card_type' => 'nullable|string|max:255',
            'last_four_digits' => 'nullable|string|size:4',
            'expiry_date' => 'nullable|date',
            'holder_name' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Get invoice and payment method
            $invoice = Invoice::findOrFail($validated['invoice_id']);
            $paymentMethod = PaymentMethod::findOrFail($validated['method_id']);

            // Calculate fee amount
            $feeAmount = ($validated['amount'] * $paymentMethod->fee_percentage) / 100;
            $netAmount = $validated['amount'] - $feeAmount;

            // Create payment record
            $payment = Payment::create([
                'invoice_id' => $validated['invoice_id'],
                'merchant_id' => $invoice->merchant_id,
                'method_id' => $validated['method_id'],
                'amount' => $validated['amount'],
                'fee_amount' => $feeAmount,
                'net_amount' => $netAmount,
                'reference_no' => 'PAY-' . Str::upper(Str::random(8)),
                'status' => 'processing',
                'paid_at' => now(),
            ]);

            // Create payment details
            PaymentDetail::create([
                'payment_id' => $payment->id,
                'method_id' => $validated['method_id'],
                'account_number' => $validated['account_number'] ?? null,
                'bank_name' => $validated['bank_name'] ?? null,
                'card_type' => $validated['card_type'] ?? null,
                'last_four_digits' => $validated['last_four_digits'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'holder_name' => $validated['holder_name'] ?? null,
            ]);

            // Update invoice status if payment successful
            $invoice->status = 'paid';
            $invoice->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'payment' => $payment->fresh(['paymentMethod', 'merchant']),
                    'reference_no' => $payment->reference_no
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified payment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $payment = Payment::with(['invoice', 'merchant', 'paymentMethod', 'paymentDetail'])->findOrFail($id);
        
        $this->authorize('view', $payment);

        // Mask sensitive data for API response
        if ($payment->paymentDetail && $payment->paymentDetail->account_number) {
            $payment->paymentDetail->masked_account_number = EncryptionHelper::maskNumber($payment->paymentDetail->account_number);
            // Don't expose the actual account number in API responses
            unset($payment->paymentDetail->account_number);
        }

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * Update the specified payment status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        
        $this->authorize('update', $payment);

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,success,failed,cancelled',
        ]);

        try {
            $payment->status = $validated['status'];
            $payment->save();

            // Update invoice status if payment is successful
            if ($validated['status'] === 'success') {
                $payment->invoice->status = 'paid';
                $payment->invoice->save();
            } elseif (in_array($validated['status'], ['failed', 'cancelled'])) {
                // Reset invoice status if payment fails or is cancelled
                $payment->invoice->status = 'pending';
                $payment->invoice->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully',
                'data' => $payment->fresh(['invoice'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
