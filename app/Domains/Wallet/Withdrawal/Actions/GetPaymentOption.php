<?php

namespace App\Domains\Wallet\Withdrawal\Actions;

class GetPaymentOption
{
    public function execute()
    {
        $user = auth()->user();

        return $user->paymentOptions()->with('bank')
            ->latest()
            ->get()
            ->map(function ($option) {
                $option->bank_name = $option->bank->name;

                return $option;
            });
    }
}
