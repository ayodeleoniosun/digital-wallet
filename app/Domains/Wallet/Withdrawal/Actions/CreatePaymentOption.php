<?php

namespace App\Domains\Wallet\Withdrawal\Actions;

use App\Domains\ThirdParty\Payment\PaymentProvider;
use App\Domains\Utils\Enums\ActivityTypesEnum;
use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Utils\Traits\ActivityTrait;
use App\Domains\Wallet\Withdrawal\Http\Requests\PaymentOptionRequest;
use App\Models\Bank;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CreatePaymentOption
{
    use ActivityTrait;

    public ?User $user = null;

    /**
     * @throws CustomException
     */
    public function execute(PaymentOptionRequest $request): Model
    {
        $this->user = auth()->user();

        $this->validatePaymentOption($request);

        $bank = Bank::where('id', $request->bank_id)->first();

        $this->verifyAccountNumber($request->account_number, $bank->code);

        $this->setActivity(ActivityTypesEnum::PAYMENT_OPTION_CREATED->value, $this->user);

        return $this->user->paymentOptions()->create([
            'bank_id' => $request->bank_id,
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
        ]);
    }

    /**
     * @throws CustomException
     */
    public function validatePaymentOption(Request $request): void
    {
        $optionExist = $this->user->paymentOptions()
            ->where('bank_id', $request->bank_id)
            ->where('account_name', $request->account_name)
            ->where('account_number', $request->account_number)
            ->first();

        if ($optionExist) {
            throw new CustomException("Payment option already added", Response::HTTP_CONFLICT);
        }
    }

    /**
     * @throws CustomException
     */
    public function verifyAccountNumber(string $accountNumber, string $bankCode): void
    {
        $paymentProvider = PaymentProvider::selectProvider();

        $verify = $paymentProvider->verifyAccountNumber($accountNumber, $bankCode);

        if (!$verify['status']) {
            throw new CustomException("Invalid account details. Try again.", Response::HTTP_BAD_REQUEST);
        }
    }
}
