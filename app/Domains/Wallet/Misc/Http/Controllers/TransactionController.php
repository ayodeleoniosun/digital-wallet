<?php

namespace App\Domains\Wallet\Misc\Http\Controllers;

use App\Domains\Utils\Enums\TransactionTypesEnum;
use App\Domains\Wallet\Deposit\Actions\DepositHistory;
use App\Domains\Wallet\Withdrawal\Actions\WithdrawalHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TransactionController
{
    /**
     */
    public function balance(Request $request): JsonResponse
    {
        $balance = number_format(auth()->user()->account->balance);

        return success('Balance retrieved', Response::HTTP_OK, $balance);
    }

    public function transactions(Request $request, string $type): JsonResponse
    {
        $transactions = null;

        if ($type == TransactionTypesEnum::DEPOSIT->value) {
            $transactions = (new DepositHistory())->execute($request);
        } elseif ($type == TransactionTypesEnum::WITHDRAWAL->value) {
            $transactions = (new WithdrawalHistory())->execute($request);
        }

        return success(ucfirst($type).' transaction history retrieved', Response::HTTP_OK, $transactions);
    }
}
