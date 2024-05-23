<?php

namespace App\Domains\Wallet\Deposit\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class DepositTransactionHistory extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'amount' => number_format($this->amount, 2),
            'reference' => $this->reference,
            'type' => $this->type,
            'deposited_on' => $this->parseDate($this->created_at),
        ];
    }

    private function parseDate($date): string
    {
        return Carbon::parse($date)->format("M dS, Y h:i a");
    }
}
