<?php

namespace App\Domains\ThirdParty\Payment;

use App\Domains\ThirdParty\Payment\Paystack\Paystack;

abstract class PaymentProvider
{
    public static function selectProvider(string $provider): self
    {
        return match ($provider) {
            'paystack' => app(Paystack::class),
        };
    }

    abstract public function getBanks();

    abstract public function createVirtualBankAccount(object $data): object;

    abstract public function initiateTransfer(object $data): object;
}
