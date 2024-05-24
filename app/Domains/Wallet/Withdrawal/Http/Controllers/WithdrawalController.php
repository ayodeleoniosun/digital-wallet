<?php

namespace App\Domains\Wallet\Withdrawal\Http\Controllers;

use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Wallet\Withdrawal\Actions\CreatePaymentOption;
use App\Domains\Wallet\Withdrawal\Actions\GetPaymentOption;
use App\Domains\Wallet\Withdrawal\Http\Requests\PaymentOptionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class WithdrawalController
{
    /**
     * @throws CustomException
     */
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

    public function withdraw(PaymentOptionRequest $request): JsonResponse
    {
        $paymentOption = (new GetPaymentOption())->execute();

        return success('Bank withdrawal details retrieved', Response::HTTP_OK, $paymentOption);
    }
}
