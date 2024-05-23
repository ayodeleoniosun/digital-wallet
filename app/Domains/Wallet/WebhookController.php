<?php

namespace App\Domains\Wallet;

use App\Domains\Wallet\Deposit\Actions\ProcessDeposit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController
{
    /**
     */
    public function deposit(Request $request): JsonResponse
    {
        Log::info("Deposit webhook => ", ['request' => $request->all()]);

        (new ProcessDeposit())->execute($request);

        return success('Deposit completed');
    }
}
