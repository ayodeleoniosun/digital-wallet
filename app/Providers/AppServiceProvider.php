<?php

namespace App\Providers;

use App\Domains\Wallet\Deposit\Events\DepositCreated;
use App\Domains\Wallet\Deposit\Listeners\DepositListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            DepositCreated::class,
            DepositListener::class,
        );
    }
}
