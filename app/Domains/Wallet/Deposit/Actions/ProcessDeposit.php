<?php

namespace App\Domains\Wallet\Deposit\Actions;

use App\Domains\ThirdParty\Payment\PaymentProvider;
use App\Domains\Utils\Enums\ActivityTypesEnum;
use App\Domains\Utils\Enums\StatusTypesEnum;
use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Utils\Traits\ActivityTrait;
use App\Domains\Wallet\Deposit\Jobs\CompleteDepositJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ProcessDeposit
{
    use ActivityTrait;

    /**
     * @throws CustomException
     */
    public function execute(Request $request): void
    {
        $data = $request->data;
        $email = $data['customer']['email'];
        $user = User::where('email', $email)->first();

        if (!$user) {
            Log::warning("User does not exist => ".$email);

            throw new CustomException("User does not exist", Response::HTTP_NOT_FOUND);
        }

        if ($data['status'] !== StatusTypesEnum::SUCCESS->value) {
            Log::warning("Deposit not successful from payment provider => ", compact('request'));

            $this->setActivity(ActivityTypesEnum::FAILED_DEPOSIT->value, $user, false);

            throw new CustomException("Deposit not successful from payment provider");
        }

        $reference = $data['reference'];

        $paymentProvider = PaymentProvider::selectProvider();

        $verifyTransaction = $paymentProvider->verifyTransaction($reference);

        if ($verifyTransaction['status'] === 'error') {
            $this->setActivity(ActivityTypesEnum::INVALID_DEPOSIT_REFERENCE->value, $user, false);

            throw new CustomException($verifyTransaction['message'], Response::HTTP_NOT_FOUND);
        }

        $uniqueId = $user->id."-".$reference;

        CompleteDepositJob::dispatch($uniqueId, $data['amount'], $reference, $user);
    }
}
