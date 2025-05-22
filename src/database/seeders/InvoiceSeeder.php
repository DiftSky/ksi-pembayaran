<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Merchant;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run()
    {
        $customers = Customer::all();
        $merchants = Merchant::all();

        for ($i = 1; $i <= 10; $i++) {
            $amount = rand(100000, 5000000);
            $taxAmount = $amount * 0.11;
            $totalAmount = $amount + $taxAmount;

            Invoice::create([
                'invoice_number' => 'INV-' . date('Y') . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'customer_id' => $customers->random()->id,
                'merchant_id' => $merchants->random()->id,
                'amount' => $amount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'due_date' => now()->addDays(rand(7, 30)),
                'status' => ['draft', 'pending', 'paid', 'overdue'][rand(0, 3)],
                'description' => 'Invoice untuk pembelian produk/jasa #' . $i
            ]);
        }
    }
}
