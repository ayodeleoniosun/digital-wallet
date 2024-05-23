<?php

namespace App\Domains\Wallet\Deposit\Actions;

use App\Domains\Wallet\Deposit\Resources\DepositTransactionHistoryCollection;
use Illuminate\Http\Request;

class GetTransactionHistory
{
    public function execute(Request $request): DepositTransactionHistoryCollection
    {
        $user = auth()->user();
        $reference = $request->reference;
        $type = $request->type;

        $deposits = $user->deposits()
            ->when($type, function ($query) use ($type) {
                $query->where('type', $type);
            })->when($reference, function ($query) use ($reference) {
                $query->where('reference', 'like', '%'.$reference.'%');
            })->latest()->paginate(10);

        return new DepositTransactionHistoryCollection($deposits);
    }
}
