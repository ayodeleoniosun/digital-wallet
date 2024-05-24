<?php

namespace App\Domains\ThirdParty\Payment\Paystack;

class Config
{
    public function baseUrl(): string
    {
        return config('services.payment.paystack.host');
    }

    public function publicKey()
    {
        return config('services.payment.paystack.public_key');
    }

    public function preferredBank()
    {
        return config('services.payment.paystack.preferred_bank');
    }

    public function secretKey()
    {
        return config('services.payment.paystack.secret_key');
    }
}
