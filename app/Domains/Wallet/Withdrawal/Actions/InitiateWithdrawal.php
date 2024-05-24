<?php

namespace App\Domains\Wallet\Withdrawal\Actions;

use App\Domains\ThirdParty\Payment\PaymentProvider;
use App\Domains\Utils\Enums\ActivityTypesEnum;
use App\Domains\Utils\Enums\WithdrawalStatusEnum;
use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Utils\Traits\ActivityTrait;
use App\Domains\Wallet\Withdrawal\Http\Requests\WithdrawalRequest;
use App\Domains\Wallet\Withdrawal\Http\Resources\Withdrawal as WithdrawalResource;
use App\Models\PaymentOption;
use App\Models\Setting;
use App\Models\User;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InitiateWithdrawal
{
    use ActivityTrait;

    public ?User $user = null;

    public ?string $amount;

    public ?string $fees;

    public object $withdrawalSettings;

    public $paymentProvider;

    public Withdrawal|Model $withdrawal;

    /**
     * @throws CustomException
     */
    public function execute(WithdrawalRequest $request): WithdrawalResource
    {
        $this->user = auth()->user();

        $this->withdrawalSettings = json_decode(Setting::where('name', 'withdraw')->value('value'));

        $this->fees = $this->withdrawalSettings->fee;

        $this->amount = $request->amount;

        $totalAmount = $this->amount + $this->fees;

        $this->validateSufficientBalance($totalAmount);

        $this->validateDailyWithdrawalLimit($totalAmount);

        $withdrawal = $this->createWithdrawal($request->reason, $request->payment_option_id);

        $this->initiateTransfer();

        return new WithdrawalResource($withdrawal);
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

    public function createWithdrawal(?string $reason, int $paymentOptionId): Model
    {
        DB::transaction(function () use ($paymentOptionId, $reason) {
            $paymentOption = PaymentOption::with('bank')->where('id', $paymentOptionId)->first();

            $this->withdrawal = $this->user->withdrawals()->create([
                'amount' => $this->amount,
                'fees' => $this->fees,
                'reference' => 'bumpa_'.Str::random(20),
                'reason' => $reason,
                'bank_name' => $paymentOption->bank->name,
                'bank_code' => $paymentOption->bank->code,
                'account_name' => $paymentOption->account_name,
                'account_number' => $paymentOption->account_number,
            ]);

            $this->setActivity(ActivityTypesEnum::WITHDRAWAL_INITIATED->value, $this->user);
        });

        Log::info("Withdrawal initiated => ", ['id' => $this->withdrawal->id]);

        return $this->withdrawal;
    }

    /**
     * @throws CustomException
     */
    public function initiateTransfer(): void
    {
        $this->paymentProvider = PaymentProvider::selectProvider();

        $createTransferRecipient = $this->paymentProvider->createTransferRecipient([
            'account_name' => $this->withdrawal->account_name,
            'account_number' => $this->withdrawal->account_number,
            'bank_code' => $this->withdrawal->bank_code,
        ]);

        if (!$createTransferRecipient['status']) {
            throw new CustomException($createTransferRecipient['message']);
        }

        $initiateTransfer = $this->paymentProvider->initiateTransfer([
            'reason' => $this->withdrawal->reason,
            'amount' => (int) $this->withdrawal->amount * 100, //convert naira to kobo for paystack
            'recipient_code' => $createTransferRecipient['data']['recipient_code'],
        ]);

        if (!$initiateTransfer['status']) {
            throw new CustomException($initiateTransfer['message']);
        }

        $this->withdrawal->transfer_code = $initiateTransfer['data']['transfer_code'];
        $this->withdrawal->status = WithdrawalStatusEnum::PROCESSING;
        $this->withdrawal->save();
    }
}
