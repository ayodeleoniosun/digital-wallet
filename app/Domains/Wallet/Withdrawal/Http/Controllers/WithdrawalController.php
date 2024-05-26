<?php

namespace App\Domains\Wallet\Withdrawal\Http\Controllers;

use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Wallet\Withdrawal\Actions\CreatePaymentOption;
use App\Domains\Wallet\Withdrawal\Actions\FinalizeWithdrawal;
use App\Domains\Wallet\Withdrawal\Actions\GetPaymentOption;
use App\Domains\Wallet\Withdrawal\Actions\InitiateWithdrawal;
use App\Domains\Wallet\Withdrawal\Actions\SetupTransactionPin;
use App\Domains\Wallet\Withdrawal\Http\Requests\FinalizeWithdrawalRequest;
use App\Domains\Wallet\Withdrawal\Http\Requests\PaymentOptionRequest;
use App\Domains\Wallet\Withdrawal\Http\Requests\WithdrawalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class WithdrawalController
{
    /**
     * @throws ValidationException
     */
    public function setupTransactionPin(Request $request): JsonResponse
    {
        $response = (new SetupTransactionPin())->execute($request);

        return success('Transaction PIN created', Response::HTTP_CREATED, $response);
    }

    public function createPaymentOption(PaymentOptionRequest $request): JsonResponse
    {
        $response = (new CreatePaymentOption())->execute($request);

        return success('Bank withdrawal details created', Response::HTTP_CREATED, $response);
    }

    public function getPaymentOption(): JsonResponse
    {
        $paymentOption = (new GetPaymentOption())->execute();

        return success('Bank withdrawal details retrieved', Response::HTTP_OK, $paymentOption);
    }

    /**
     * @throws CustomException
     */
    public function withdraw(WithdrawalRequest $request): JsonResponse
    {
        $response = (new InitiateWithdrawal())->execute($request);

        return success('Bank withdrawal initiated. Kindly input the 6 digits OTP sent to your mobile phone to finalize the withdrawal',
            Response::HTTP_CREATED,
            $response);
    }

    /**
     * @throws CustomException
     */
    public function finalize(FinalizeWithdrawalRequest $request): JsonResponse
    {
        $response = (new FinalizeWithdrawal())->execute($request);

        return success('Bank withdrawal completed. You would be credited within the next 1 hour',
            Response::HTTP_OK,
            $response);
    }
}
