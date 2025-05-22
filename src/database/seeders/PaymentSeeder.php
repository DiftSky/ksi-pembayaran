<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\PaymentDetail;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run()
    {
        $invoices = Invoice::where('status', 'paid')->get();
        $paymentMethods = PaymentMethod::all();

        foreach ($invoices as $invoice) {
            $method = $paymentMethods->random();
            $feeAmount = ($invoice->total_amount * $method->fee_percentage) / 100;
            $netAmount = $invoice->total_amount - $feeAmount;

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'merchant_id' => $invoice->merchant_id,
                'method_id' => $method->id,
                'amount' => $invoice->total_amount,
                'fee_amount' => $feeAmount,
                'net_amount' => $netAmount,
                'reference_no' => 'PAY-' . date('Y') . '-' . uniqid(),
                'gateway_response' => [
                    'transaction_id' => 'TXN-' . uniqid(),
                    'status' => 'success',
                    'message' => 'Payment successful'
                ],
                'status' => 'success',
                'paid_at' => now()
            ]);

            // Create payment detail
            $this->createPaymentDetail($payment, $method);
        }
    }

    private function createPaymentDetail($payment, $method)
    {
        // Create a new PaymentDetail model manually so we can use its mutators for encryption
        $detail = new PaymentDetail();
        $detail->payment_id = $payment->id;
        $detail->method_id = $method->id;

        switch ($method->type) {
            case 'bank_transfer':
                $detail->account_number = '1234567890';
                $detail->bank_name = $method->provider;
                $detail->holder_name = 'Account Holder';
                break;
            case 'credit_card':
            case 'debit_card':
                $detail->card_type = $method->provider;
                $detail->last_four_digits = '1234';
                $detail->expiry_date = now()->addYears(2);
                $detail->holder_name = 'Card Holder';
                break;
            case 'e_wallet':
                $detail->account_number = '081234567890';
                $detail->holder_name = 'Wallet Owner';
                break;
            case 'virtual_account':
                $detail->account_number = '8877' . rand(1000000000, 9999999999);
                $detail->bank_name = $method->provider;
                break;
        }

        $detail->save();
    }
}
