<?php

namespace App\Domains\Wallet\Withdrawal\Actions;

use App\Domains\Wallet\Withdrawal\Http\Resources\WithdrawalTransactionHistoryCollection;
use Illuminate\Http\Request;

class WithdrawalHistory
{
    public function execute(Request $request): WithdrawalTransactionHistoryCollection
    {
        $user = auth()->user();
        $reference = $request->reference;
        $transferCode = $request->transfer_code;
        $accountNumber = $request->account_number;
        $accountName = $request->account_name;

        $deposits = $user->withdrawals()
            ->when($reference, function ($query) use ($reference) {
                $query->where('reference', 'like', '%'.$reference.'%');
            })->when($accountNumber, function ($query) use ($accountNumber) {
                $query->where('account_number', 'like', '%'.$accountNumber.'%');
            })->when($accountName, function ($query) use ($accountName) {
                $query->where('account_name', 'like', '%'.$accountName.'%');
            })->when($transferCode, function ($query) use ($transferCode) {
                $query->where('transfer_code', 'like', '%'.$transferCode.'%');
            })->latest()->paginate(10);

        return new WithdrawalTransactionHistoryCollection($deposits);
    }
}
