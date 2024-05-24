<?php

namespace App\Domains\Wallet\Withdrawal\Actions;

use App\Domains\Utils\Enums\ActivityTypesEnum;
use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Utils\Traits\ActivityTrait;
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
    public function execute(Request $request): Model
    {
        $this->user = auth()->user();

        $this->validatePaymentOption($request);

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
}
