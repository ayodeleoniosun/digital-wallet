<?php

namespace App\Domains\Wallet\Http\Controllers;

use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Wallet\Deposit\Actions\Deposit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController
{
    /**
     * @throws CustomException
     */
    public function deposit(Request $request): JsonResponse
    {
        Log::info("Deposit webhook => ", ['request' => $request->all()]);

        (new Deposit())->execute($request);

        return success('Deposit is being processed');
    }
}
