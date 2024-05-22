<?php

namespace App\Domains\ThirdParty\Payment\Paystack;

class Config
{
    public function baseUrl(): string
    {
        return config('services.paystack.host');
    }

    public function publicKey()
    {
        return config('services.paystack.public_key');
    }

    public function secretKey()
    {
        return config('services.paystack.secret_key');
    }
}
