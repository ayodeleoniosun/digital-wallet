<?php

namespace App\Domains\ThirdParty\Payment;

use App\Domains\ThirdParty\Payment\Paystack\Paystack;

abstract class PaymentProvider
{
    public static function selectProvider(): self
    {
        $provider = config('services.payment.provider');

        return match ($provider) {
            'paystack' => app(Paystack::class),
        };
    }

    abstract public function createVirtualBankAccount(object $data): array;

    abstract public function initiateTransfer(array $data): array;

    abstract public function finalizeTransfer(array $data): object;

    abstract public function verifyTransaction(string $reference): array;

    abstract public function listBanks(string $country = 'nigeria'): array;

    abstract public function createTransferRecipient(array $data): array;

    abstract public function verifyAccountNumber(string $accountNumber, string $bankCode): array;
}
