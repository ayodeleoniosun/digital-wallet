<?php

namespace Tests\Domains\Wallet\Withdrawal\Actions;

use App\Domains\Utils\Enums\ActivityTypesEnum;
use App\Domains\Utils\Enums\WithdrawalStatusEnum;
use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Wallet\Withdrawal\Actions\InitiateWithdrawal;
use App\Domains\Wallet\Withdrawal\Http\Requests\WithdrawalRequest;
use App\Domains\Wallet\Withdrawal\Http\Resources\Withdrawal;
use App\Models\User;
use Database\Seeders\BanksSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Facades\Redis;

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

    $this->request = new WithdrawalRequest();

    $this->payload = [
        'payment_option_id' => $this->paymentOption->id,
        'currency' => 'NGN',
        'amount' => 35000,
    ];

    $this->request->merge($this->payload);
});

it('should throw an error if user has insufficient balance', function () {
    $this->payload['amount'] = 500000000;
    $this->request->merge($this->payload);

    (new InitiateWithdrawal())->execute($this->request);
})->throws(CustomException::class, 'Insufficient Balance');

it('should throw an error if amount exceeds daily limit', function () {
    (new InitiateWithdrawal())->execute($this->request);

    $this->payload['amount'] = 450000;
    $this->request->merge($this->payload);

    (new InitiateWithdrawal())->execute($this->request);

})->throws(CustomException::class);

it('should throw an error if double withdrawal is initiated', function () {
    (new InitiateWithdrawal())->execute($this->request);

    (new InitiateWithdrawal())->execute($this->request);
})->throws(CustomException::class, 'Double withdrawal spotted. Try again in a minute time.');

it('should complete withdrawal ', function () {
    $reference = $this->user->id.'-'.$this->payload['amount'];
    Redis::del($reference);
    
    $response = (new InitiateWithdrawal())->execute($this->request);

    expect($response)->toBeInstanceOf(Withdrawal::class)
        ->and($response->amount)->toBe((string) $this->payload['amount'])
        ->and($response->user_id)->toBe($this->user->id)
        ->and($response->bank_name)->toBe($this->paymentOption->bank->name)
        ->and($response->bank_code)->toBe($this->paymentOption->bank->code)
        ->and($response->account_name)->toBe($this->paymentOption->account_name)
        ->and($response->account_number)->toBe($this->paymentOption->account_number)
        ->and($response->status)->toBe(WithdrawalStatusEnum::PROCESSING);

    $this->assertDatabaseHas('withdrawals', [
        'user_id' => $this->user->id,
    ]);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $this->user->id,
        'type' => ActivityTypesEnum::WITHDRAWAL_INITIATED->value,
    ]);
});
