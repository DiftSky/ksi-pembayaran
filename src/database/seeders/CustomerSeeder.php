<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        $customers = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '081234567890',
                'address' => 'Jl. Sudirman No. 123',
                'city' => 'Jakarta',
                'state' => 'DKI Jakarta',
                'postal_code' => '10220',
                'country' => 'Indonesia',
                'notes' => 'Regular customer'
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'phone' => '082345678901',
                'address' => 'Jl. Thamrin No. 456',
                'city' => 'Jakarta',
                'state' => 'DKI Jakarta',
                'postal_code' => '10350',
                'country' => 'Indonesia',
                'notes' => 'VIP customer'
            ],
            [
                'name' => 'David Wilson',
                'email' => 'david@example.com',
                'phone' => '083456789012',
                'address' => 'Jl. Gatot Subroto No. 789',
                'city' => 'Jakarta',
                'state' => 'DKI Jakarta',
                'postal_code' => '10270',
                'country' => 'Indonesia',
                'notes' => null
            ],
            [
                'name' => 'Maria Garcia',
                'email' => 'maria@example.com',
                'phone' => '084567890123',
                'address' => 'Jl. Kuningan No. 101',
                'city' => 'Jakarta',
                'state' => 'DKI Jakarta',
                'postal_code' => '12940',
                'country' => 'Indonesia',
                'notes' => 'Corporate client'
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
