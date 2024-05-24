<?php

namespace Tests\Domains\Wallet\Withdrawal\Actions;

use App\Domains\Utils\Enums\ActivityTypesEnum;
use App\Domains\Utils\Enums\TransactionStatusEnum;
use App\Domains\Utils\Enums\TransactionTypesEnum;
use App\Domains\Utils\Enums\WithdrawalStatusEnum;
use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Wallet\Withdrawal\Actions\FinalizeWithdrawal;
use App\Domains\Wallet\Withdrawal\Actions\InitiateWithdrawal;
use App\Domains\Wallet\Withdrawal\Http\Requests\WithdrawalRequest;
use App\Domains\Wallet\Withdrawal\Http\Resources\Withdrawal;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\BanksSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->user->profile()->create([
        'user_id' => $this->user->id,
        'unique_id' => uniquePrefix(),
        'phone' => '+2348123456789',
    ]);

    $this->seed(BanksSeeder::class);

    $this->paymentOption = $this->user->paymentOptions()->create([
        'bank_id' => 160, //zenith bank
        'account_name' => 'Test',
        'account_number' => '0000000000',
    ]);

    $this->user->account()->create([
        'balance' => 600000,
    ]);

    $this->actingAs($this->user);

    $this->seed(SettingsSeeder::class);

    $this->withdrawalRequest = new WithdrawalRequest();

    $this->withdrawalPayload = [
        'payment_option_id' => $this->paymentOption->id,
        'currency' => 'NGN',
        'amount' => 35000,
    ];

    $this->withdrawalRequest->merge($this->withdrawalPayload);

    $this->finalizeTransferRequest = new Request();

    $this->finalizeTransferPayload = [
        'transfer_code' => Str::random(),
        'otp' => '123456',
    ];

    $this->finalizeTransferRequest->merge($this->finalizeTransferPayload);
});

it('should throw an error if transfer code is invalid', function () {
    (new FinalizeWithdrawal())->execute($this->finalizeTransferRequest);
})->throws(CustomException::class, 'Transfer code is invalid');

it('should finalize user withdrawal', function () {
    $withdrawal = (new InitiateWithdrawal())->execute($this->withdrawalRequest);

    $this->finalizeTransferPayload['transfer_code'] = $withdrawal->transfer_code;
    $this->finalizeTransferRequest->merge($this->finalizeTransferPayload);

    $response = (new FinalizeWithdrawal())->execute($this->finalizeTransferRequest);

    $settings = json_decode(Setting::where('name', 'withdraw')->value('value'));

    $balance = 600000 - ($this->withdrawalPayload['amount'] + $settings->fee);

    expect($response)->toBeInstanceOf(Withdrawal::class)
        ->and($response->amount)->toBe((string) $this->withdrawalPayload['amount'])
        ->and($response->user_id)->toBe($this->user->id)
        ->and($response->bank_name)->toBe($this->paymentOption->bank->name)
        ->and($response->bank_code)->toBe($this->paymentOption->bank->code)
        ->and($response->account_name)->toBe($this->paymentOption->account_name)
        ->and($response->account_number)->toBe($this->paymentOption->account_number)
        ->and($response->status)->toBe(WithdrawalStatusEnum::SUCCESSFUL)
        ->and($this->user->account->balance)->toBe((string) $balance);

    $this->assertDatabaseHas('accountings', [
        'user_id' => $this->user->id,
        'type' => TransactionTypesEnum::WITHDRAWAL->value,
        'status' => TransactionStatusEnum::SUCCESSFUL->value,
        'accountable_type' => \App\Models\Withdrawal::class,
        'accountable_id' => $withdrawal->id,
    ]);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $this->user->id,
        'type' => ActivityTypesEnum::WITHDRAWAL_COMPLETED->value,
    ]);
});

