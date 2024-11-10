<?php

namespace Database\Seeders;

use App\Models\Payment_Methode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Payment_Methode::insert([
            [
                'payment_method' => 'Cash',
            ],
            [
                'payment_method' => 'Credit Card',
            ],
            [
                'payment_method' => 'Paypal',
            ],
            [
                'payment_method' => 'E-wallet',
            ]
        ]);
    }
}
