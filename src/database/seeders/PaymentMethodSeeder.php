<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run()
    {
        $paymentMethods = [
            [
                'name' => 'Bank Transfer BCA',
                'code' => 'BCA_TRANSFER',
                'type' => 'bank_transfer',
                'provider' => 'BCA',
                'is_active' => true,
                'fee_percentage' => 0.50
            ],
            [
                'name' => 'Credit Card Visa',
                'code' => 'VISA_CC',
                'type' => 'credit_card',
                'provider' => 'Visa',
                'is_active' => true,
                'fee_percentage' => 2.95
            ],
            [
                'name' => 'GoPay',
                'code' => 'GOPAY',
                'type' => 'e_wallet',
                'provider' => 'Gojek',
                'is_active' => true,
                'fee_percentage' => 1.50
            ],
            [
                'name' => 'OVO',
                'code' => 'OVO',
                'type' => 'e_wallet',
                'provider' => 'OVO',
                'is_active' => true,
                'fee_percentage' => 1.25
            ],
            [
                'name' => 'Virtual Account Mandiri',
                'code' => 'MANDIRI_VA',
                'type' => 'virtual_account',
                'provider' => 'Mandiri',
                'is_active' => true,
                'fee_percentage' => 0.75
            ]
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }
    }
}
