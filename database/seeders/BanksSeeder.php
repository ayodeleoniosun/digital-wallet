<?php

namespace Database\Seeders;

use App\Domains\ThirdParty\Payment\PaymentProvider;
use App\Models\Bank;
use Kdabrow\SeederOnce\SeederOnce;

class BanksSeeder extends SeederOnce
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentProvider = PaymentProvider::selectProvider();

        $banks = $paymentProvider->getBanks();

        foreach ($banks['data'] as $bank) {
            $bankExist = Bank::where('name', $bank['name'])->first();

            if (!$bankExist) {
                Bank::create([
                    'name' => $bank['name'],
                    'code' => $bank['code'],
                ]);
            }
        }
    }
}
