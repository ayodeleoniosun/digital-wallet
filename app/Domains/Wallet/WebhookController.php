<?php

namespace App\Domains\Wallet;

use App\Domains\Wallet\Deposit\Actions\ProcessDeposit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController
{
    /**
     */
    public function deposit(Request $request): JsonResponse
    {
        (new ProcessDeposit())->execute($request);

        return successResponse('Deposit completed');
    }
}
