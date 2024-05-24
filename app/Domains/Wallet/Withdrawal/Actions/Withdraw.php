<?php

namespace App\Domains\Wallet\Withdrawal\Actions;

use App\Domains\Utils\Enums\TransactionStatusEnum;
use App\Domains\Utils\Enums\TransactionTypesEnum;
use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Utils\Traits\ActivityTrait;
use App\Domains\Wallet\Withdrawal\Http\Requests\WithdrawalRequest;
use App\Models\PaymentOption;
use App\Models\Setting;
use App\Models\User;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Withdraw
{
    use ActivityTrait;

    public ?User $user = null;

    public ?string $amount;

    public ?string $fees;

    public ?string $reference;

    public $withdrawalSettings;

    /**
     * @throws CustomException
     */
    public function execute(WithdrawalRequest $request): Model
    {
        $this->user = auth()->user();

        $this->amount = $request->amount;

        $this->withdrawalSettings = json_decode(Setting::where('name', 'withdraw')->value('value'));

        $this->fees = $this->withdrawalSettings->fee;

        $totalAmount = $this->amount + $this->fees;

        $this->validateMaxWithdrawal($totalAmount);

        $this->validateSufficientBalance($totalAmount);

        $this->validateDailyWithdrawalLimit($totalAmount);

        $this->reference = 'bumpa_'.Str::random(10);

        return $this->processWithdrawal($request->payment_option_id);
    }

    /**
     * @throws CustomException
     */
    public function validateMaxWithdrawal(int $amount): void
    {
        $withdrawalLimit = $this->withdrawalSettings->withdraw_limit;

        if ($amount > $withdrawalLimit) {
            throw new CustomException("Amount to be withdrawn must not exceed ".number_format($withdrawalLimit));
        }
    }

    /**
     * @throws CustomException
     */
    public function validateSufficientBalance(string $amount): void
    {
        if ($this->user->account->balance < $amount) {
            throw new CustomException("Insufficient Balance");
        }
    }

    /**
     * @throws CustomException
     */
    public function validateDailyWithdrawalLimit(string $amount): void
    {
        $withdrawalsForToday = $this->user->withdrawals()
            ->selectRaw('sum(amount) as total_amount')
            ->whereDate('created_at', Carbon::today())
            ->get()->value('total_amount');

        $totalWithdrawals = $withdrawalsForToday + $amount;
        $dailyLimit = $this->withdrawalSettings->daily_limit;

        if ($totalWithdrawals > $dailyLimit) {
            throw new CustomException("Daily withdrawal limit of ".number_format($dailyLimit, 2)." exceeded");
        }
    }

    public function processWithdrawal(int $paymentOptionId): Model
    {
        $withdrawal = null;

        DB::transaction(function () use ($paymentOptionId, &$withdrawal) {
            $totalAmount = $this->amount + $this->fees;

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

            $paymentOption = PaymentOption::with('bank')->where('id', $paymentOptionId)->first();

            $withdrawal = $this->user->withdrawals()->create([
                'amount' => $this->amount,
                'fees' => $this->fees,
                'reference' => $this->reference,
                'bank_name' => $paymentOption->bank->name,
                'account_name' => $paymentOption->account_name,
                'account_number' => $paymentOption->account_number,
            ]);

            $this->user->accountings()->create([
                'amount' => $totalAmount,
                'type' => TransactionTypesEnum::WITHDRAWAL->value,
                'status' => TransactionStatusEnum::PENDING->value,
                'accountable_type' => Withdrawal::class,
                'accountable_id' => $withdrawal->id,
            ]);
        });

        Log::info("Withdrawal initiated => ", [
            'reference' => $this->reference,
            'user_id' => $this->user->id,
        ]);

        return $withdrawal;
    }
}
