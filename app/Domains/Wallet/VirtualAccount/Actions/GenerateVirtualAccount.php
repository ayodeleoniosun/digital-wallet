<?php

namespace App\Domains\Wallet\VirtualAccount\Actions;

use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Utils\Traits\ActivityTrait;
use App\Domains\Wallet\VirtualAccount\Actions\Paystack\CreateVirtualAccount;
use App\Models\User;
use App\Models\VirtualAccount;

class GenerateVirtualAccount
{
    use ActivityTrait;

    private ?User $user = null;

    private $provider;

    private $paymentProvider;

    private ?string $selectedProvider = null;

    public function __construct()
    {
        $this->selectedProvider = config('services.payment.provider');
    }

    /**
     * @throws CustomException
     */
    public function execute(): User
    {
        $this->user = auth()->user();

        $this->validateVirtualAccount();

        if ($this->selectedProvider === 'paystack') {
            return (new CreateVirtualAccount())->generateVirtualAccount();
        }
    }

    /**
     * @throws CustomException
     */
    public function validateVirtualAccount(): ?VirtualAccount
    {
        $account = $this->user->virtualAccount()
            ->where('provider', $this->selectedProvider)
            ->whereNull('deleted_at')
            ->first();

        if ($account) {
            throw new CustomException('Virtual account details already generated');
        }

        return $account;
    }
}
