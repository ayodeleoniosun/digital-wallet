<?php

namespace App\Domains\Wallet\Withdrawal\Jobs;

use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Utils\Traits\ActivityTrait;
use App\Domains\Wallet\Withdrawal\Http\Requests\WithdrawalRequest;
use App\Models\User;
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

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $reference,
        public WithdrawalRequest $request,
        public string $fees,
        public User $user,
    ) {
        $this->expiration = 120; // 2 minutes
    }

    public function uniqueId(): string
    {
        return $this->reference;
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->reference))->releaseAfter(30)];
    }

    /**
     * Execute the job.
     *
     * @throws CustomException
     */
    public function handle(): void
    {

    }
}
