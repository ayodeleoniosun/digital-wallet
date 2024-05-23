<?php

namespace App\Domains\Wallet\Deposit\Listeners;

use App\Domains\Wallet\Deposit\Events\DepositCreated;
use App\Domains\Wallet\Deposit\Mail\SendDepositMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class DepositListener implements ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DepositCreated $event): void
    {
        $this->deposit = $event->deposit;

        Mail::to($this->deposit->user)->queue(new SendDepositMail($this->deposit));
    }
}
