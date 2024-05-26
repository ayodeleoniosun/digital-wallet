<?php

namespace App\Domains\Wallet\Deposit\Actions;

use App\Domains\Wallet\Deposit\Http\Resources\DepositTransactionHistoryCollection;
use Illuminate\Http\Request;

class DepositHistory
{
    public function execute(Request $request): DepositTransactionHistoryCollection
    {
        $user = auth()->user();
        $reference = $request->reference;
        
        $deposits = $user->deposits()
            ->when($reference, function ($query) use ($reference) {
                $query->where('reference', 'like', '%'.$reference.'%');
            })->latest()->paginate(10);

        return new DepositTransactionHistoryCollection($deposits);
    }
}
