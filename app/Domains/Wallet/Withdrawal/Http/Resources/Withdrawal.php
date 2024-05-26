<?php

namespace App\Domains\Wallet\Withdrawal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class Withdrawal extends JsonResource
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
            'fees' => number_format($this->fees, 2),
            'bank_name' => $this->bank_name,
            'bank_code' => $this->bank_code,
            'account_name' => $this->account_name,
            'account_number' => $this->account_number,
            'transfer_code' => $this->transfer_code,
            'reference' => $this->reference,
            'provider_reference' => $this->provider_reference,
            'status' => ucfirst($this->status->value),
            'withdrawn_on' => $this->parseDate($this->created_at),
        ];
    }

    private function parseDate($date): string
    {
        return Carbon::parse($date)->format("M dS, Y h:i a");
    }
}
