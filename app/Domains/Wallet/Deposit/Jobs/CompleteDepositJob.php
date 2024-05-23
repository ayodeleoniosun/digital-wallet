<?php

namespace App\Domains\Wallet\Deposit\Jobs;

use App\Domains\Utils\Enums\DepositTypesEnum;
use App\Domains\Utils\Enums\TransactionStatusEnum;
use App\Domains\Utils\Enums\TransactionTypesEnum;
use App\Domains\Utils\Traits\ActivityTrait;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class  CompleteDepositJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ActivityTrait;

    public int $expiration;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $uniqueId,
        public string $amount,
        public string $reference,
        public User $user,
    ) {
        $this->expiration = 300; // 5 minutes
    }

    public function uniqueId(): string
    {
        return $this->uniqueId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::transaction(function () use (&$deposit) {
            $previousBalance = $this->user->account->balance;

            $this->user->account->balance += $this->amount;
            $this->user->account->save();

            $this->user->account->refresh();

            $this->user->ledgers()->create([
                'previous_balance' => $previousBalance,
                'new_balance' => $this->user->account->balance,
            ]);

            $deposit = $this->user->deposits()->create([
                'amount' => $this->amount,
                'reference' => $this->reference,
                'type' => DepositTypesEnum::EXTERNAL->value,
            ]);

            $this->user->accountings()->create([
                'amount' => $this->amount,
                'type' => TransactionTypesEnum::DEPOSIT->value,
                'status' => TransactionStatusEnum::SUCCESSFUL->value,
                'accountable_type' => Deposit::class,
                'accountable_id' => $deposit->id,
            ]);
        });

        Redis::set($this->uniqueId, true, 'EX', $this->expiration);

        Log::info("Deposit completed => ", [
            'reference' => $this->reference,
            'user_id' => $this->user->id,
        ]);

        $this->setActivity('deposit-completed', $this->user, false);
    }
}
