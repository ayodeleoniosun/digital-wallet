<?php

namespace App\Domains\Wallet\Misc\Actions;

use Illuminate\Http\Request;

class GetBalance
{
    public function execute(Request $request)
    {
        return auth()->user()->account->balance;
    }
}
