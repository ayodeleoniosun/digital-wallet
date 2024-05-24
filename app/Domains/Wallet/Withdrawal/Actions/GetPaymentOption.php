<?php

namespace App\Domains\Wallet\Withdrawal\Actions;

class GetPaymentOption
{
    public function execute()
    {
        $user = auth()->user();

        return tap($user->paymentOptions()->with('bank')->latest()->first(), function ($option) {
            $option->bank_name = $option->bank->name;

            return $option;
        });
    }
}
