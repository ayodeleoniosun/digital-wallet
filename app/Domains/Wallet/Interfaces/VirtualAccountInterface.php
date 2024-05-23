<?php

namespace App\Domains\Wallet\Interfaces;

use App\Models\User;

interface VirtualAccountInterface
{
    public function generateVirtualAccount(): User;
}
