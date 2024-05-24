<?php

namespace App\Domains\Wallet\Withdrawal\Jobs;

use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Utils\Traits\ActivityTrait;
use App\Models\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class  CompleteWithdrawalJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ActivityTrait;

    public int $expiration;

    public $paymentProvider;

    /**
     * Create a new job instance.
     */
    public function __construct(public Withdrawal $withdrawal)
    {
        $this->expiration = 120; // 2 minutes
    }

    public function uniqueId(): string
    {
        return $this->withdrawal->reference;
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->withdrawal->reference))->releaseAfter(30)];
    }

    /**
     * Execute the job.
     *
     */
    public function handle(): void
    {
        $this->initiateTransfer($this->withdrawal->account_number, $this->withdrawal->bank_code);
    }

    /**
     * @throws CustomException
     */

}
