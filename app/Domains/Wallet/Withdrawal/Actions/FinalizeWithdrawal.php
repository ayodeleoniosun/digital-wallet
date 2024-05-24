<?php

namespace App\Domains\Wallet\Withdrawal\Actions;

use App\Domains\ThirdParty\Payment\PaymentProvider;
use App\Domains\Utils\Enums\ActivityTypesEnum;
use App\Domains\Utils\Enums\TransactionStatusEnum;
use App\Domains\Utils\Enums\TransactionTypesEnum;
use App\Domains\Utils\Enums\WithdrawalStatusEnum;
use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Utils\Traits\ActivityTrait;
use App\Domains\Wallet\Withdrawal\Http\Resources\Withdrawal as WithdrawalResource;
use App\Models\Withdrawal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinalizeWithdrawal
{
    use ActivityTrait;

    public $paymentProvider;

    public Withdrawal|Model $withdrawal;

    public array $finalizeWithdrawal;

    /**
     * @throws CustomException
     */
    public function execute(Request $request): WithdrawalResource
    {
        $this->user = auth()->user();

        $transferCode = $request->transfer_code;

        $this->paymentProvider = PaymentProvider::selectProvider();

        $this->finalizeWithdrawal = $this->paymentProvider->finalizeTransfer([
            'transfer_code' => $transferCode,
            'otp' => $request->otp,
        ]);

        if (!$this->finalizeWithdrawal['status']) {
            throw new CustomException($this->finalizeWithdrawal['message']);
        }

        $this->withdrawal = Withdrawal::where('transfer_code', $transferCode)->first();

        if (!$this->withdrawal) {
            throw new CustomException("Withdrawal does not exist.");
        }

        $withdrawal = $this->finalizeTransfer();

        return new WithdrawalResource($withdrawal);
    }

    /**
     * @throws CustomException
     */
    public function finalizeTransfer(): Model|Withdrawal
    {
        DB::transaction(function () {
            $totalAmount = $this->withdrawal->amount + $this->withdrawal->fees;

            $this->user->account()->lockForUpdate();

            $previousBalance = $this->user->account->balance;

            $this->user->account->balance -= $totalAmount;
            $this->user->account->save();

            $this->user->account->refresh();

            $this->user->ledgers()->create([
                'previous_balance' => $previousBalance,
                'new_balance' => $this->user->account->balance,
                'type' => TransactionTypesEnum::WITHDRAWAL->value,
            ]);

            $this->user->accountings()->create([
                'amount' => $totalAmount,
                'type' => TransactionTypesEnum::WITHDRAWAL->value,
                'status' => TransactionStatusEnum::SUCCESSFUL->value,
                'accountable_type' => Withdrawal::class,
                'accountable_id' => $this->withdrawal->id,
            ]);

            $this->setActivity(ActivityTypesEnum::WITHDRAWAL_COMPLETED->value, $this->user);

            $this->withdrawal->status = WithdrawalStatusEnum::SUCCESSFUL;
            $this->withdrawal->provider_reference = $this->finalizeWithdrawal['reference'];
            $this->withdrawal->save();
        });

        Log::info("Withdrawal completed => ", ['id' => $this->withdrawal->id]);

        return $this->withdrawal;
    }
}
