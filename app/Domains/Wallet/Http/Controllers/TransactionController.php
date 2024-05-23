<?php

namespace App\Domains\Wallet\Http\Controllers;

use App\Domains\Utils\Enums\TransactionTypesEnum;
use App\Domains\Wallet\Deposit\Actions\GetTransactionHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TransactionController
{
    /**
     */
    public function transactions(Request $request, string $type): JsonResponse
    {
        $transactions = null;

        if ($type == TransactionTypesEnum::DEPOSIT->value) {
            $transactions = (new GetTransactionHistory())->execute($request);
        } elseif ($type == TransactionTypesEnum::WITHDRAWAL->value) {
            $transactions = (new \App\Domains\Wallet\Withdrawal\Actions\GetTransactionHistory())->execute($request);
        }

        return success('Transaction history retrieved', Response::HTTP_OK, $transactions);
    }
}
