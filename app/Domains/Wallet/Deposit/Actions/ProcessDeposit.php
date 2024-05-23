<?php

namespace App\Domains\Wallet\Deposit\Actions;

use App\Domains\ThirdParty\Payment\PaymentProvider;
use App\Domains\Utils\Enums\DepositTypesEnum;
use App\Domains\Utils\Enums\StatusTypesEnum;
use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Utils\Traits\ActivityTrait;
use App\Domains\Wallet\Deposit\Jobs\CompleteDepositJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

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

            throw new CustomException("User does not exist");
        }

        if ($data['status'] !== StatusTypesEnum::SUCCESS->value) {
            Log::warning("Deposit not successful from payment provider => ", compact('request'));

            $this->setActivity('failed-deposit', $user, false);

            throw new CustomException("Deposit not successful from payment provider");
        }

        $reference = $data['reference'];

        $paymentProvider = PaymentProvider::selectProvider();

        $verifyTransaction = $paymentProvider->verifyTransaction($reference);

        if (!$verifyTransaction['status']) {
            $this->setActivity('invalid-deposit-reference', $user, false);

            throw new CustomException($verifyTransaction['message']);
        }

        $uniqueId = $user->id."-".$reference;

        $this->validateDuplicateDeposit($uniqueId, $reference, $user);

        CompleteDepositJob::dispatch($uniqueId, $data['amount'], $reference, $user);
    }

    /**
     * @throws CustomException
     */
    public function validateDuplicateDeposit(string $uniqueId, string $reference, User $user): bool
    {
        $depositExistInRedis = Redis::get($uniqueId);

        $deposit = $user->deposits()
            ->where('reference', $reference)
            ->where('type', DepositTypesEnum::INTERNAL->value)
            ->first();

        if ($deposit || !empty($depositExistInRedis)) {
            Log::warning("Deposit already completed => ", [
                'reference' => $reference,
                'user_id' => $user->id,
            ]);

            $this->setActivity('deposit-already-exist', $user, false);

            throw new CustomException("Deposit already completed");
        }

        return true;
    }
}
