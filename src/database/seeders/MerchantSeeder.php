<?php

namespace Database\Seeders;

use App\Models\Merchant;
use Illuminate\Database\Seeder;

class MerchantSeeder extends Seeder
{
    public function run()
    {
        $merchants = [
            [
                'business_name' => 'Tech Solutions Inc',
                'owner_name' => 'Ahmad Wijaya',
                'email' => 'info@techsolutions.com',
                'phone' => '021-12345678',
                'address' => 'Jl. HR Rasuna Said No. 100, Jakarta',
                'category' => 'Technology',
                'status' => true
            ],
            [
                'business_name' => 'Fashion Store',
                'owner_name' => 'Siti Nurhaliza',
                'email' => 'contact@fashionstore.com',
                'phone' => '021-87654321',
                'address' => 'Jl. Menteng Raya No. 50, Jakarta',
                'category' => 'Fashion',
                'status' => true
            ],
            [
                'business_name' => 'Food Corner',
                'owner_name' => 'Budi Santoso',
                'email' => 'hello@foodcorner.com',
                'phone' => '021-11223344',
                'address' => 'Jl. Kemang No. 25, Jakarta',
                'category' => 'Food & Beverage',
                'status' => true
            ]
        ];

        foreach ($merchants as $merchant) {
            Merchant::create($merchant);
        }
    }
}
