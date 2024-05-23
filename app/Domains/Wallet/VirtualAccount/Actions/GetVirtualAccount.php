<?php

namespace App\Domains\Wallet\VirtualAccount\Actions;

use App\Domains\Utils\Exceptions\CustomException;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetVirtualAccount
{
    private ?User $user = null;

    /**
     * @throws CustomException
     */
    public function execute(): Collection
    {
        $this->user = auth()->user();

        $virtualAccounts = $this->user->virtualAccounts()->get();

        if ($virtualAccounts->count() === 0) {
            return (new GenerateVirtualAccount())->execute()
                ->virtualAccounts()
                ->get();
        }

        return $virtualAccounts;
    }
}
