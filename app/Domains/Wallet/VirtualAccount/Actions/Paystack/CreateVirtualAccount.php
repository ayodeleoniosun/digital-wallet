<?php

namespace App\Domains\Wallet\VirtualAccount\Actions\Paystack;

use App\Domains\ThirdParty\Payment\Paystack\Paystack;
use App\Domains\Utils\Enums\ActivityTypesEnum;
use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Utils\Traits\ActivityTrait;
use App\Domains\Wallet\Interfaces\VirtualAccountInterface;
use App\Models\User;

class CreateVirtualAccount implements VirtualAccountInterface
{
    use ActivityTrait;

    private ?User $user = null;

    /**
     * @throws CustomException
     */
    public function generateVirtualAccount(): User
    {
        $this->user = auth()->user();

        $data = (object) [
            'firstname' => $this->user->firstname,
            'lastname' => $this->user->lastname,
            'email' => $this->user->email,
            'phone' => $this->user->profile->phone,
        ];

        $account = (new Paystack())->createVirtualBankAccount($data);

        if (!$account['status']) {
            throw new CustomException($account['message']);
        }

        $this->user->virtualAccounts()->create([
            'bank_name' => $account['data']['bank']['name'],
            'account_name' => $account['data']['account_name'],
            'account_number' => $account['data']['account_number'],
            'reference' => $account['data']['id'],
            'provider' => config('services.payment.provider'),
        ]);

        $this->setActivity(ActivityTypesEnum::CREATE_VIRTUAL_ACCOUNT->value, $this->user);

        return $this->user->with('virtualAccounts')->first();
    }
}
