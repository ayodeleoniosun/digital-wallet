<?php

namespace Tests\Domains\Wallet\Withdrawal\Http\Controllers;

use App\Domains\Utils\Enums\StatusTypesEnum;
use App\Domains\Wallet\Withdrawal\Http\Requests\PaymentOptionRequest;
use App\Domains\Wallet\Withdrawal\Http\Requests\WithdrawalRequest;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\BanksSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redis;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->user->profile()->create([
        'user_id' => $this->user->id,
        'unique_id' => uniquePrefix(),
        'phone' => '+2348123456789',
    ]);

    $this->user->account()->create([
        'balance' => 50000,
    ]);

    $this->actingAs($this->user);

    $this->seed(BanksSeeder::class);

    $this->seed(SettingsSeeder::class);

    $this->paymentOptionRequest = new PaymentOptionRequest();

    $this->paymentOptionPayload = [
        'bank_id' => 160, //zenith bank
        'account_name' => 'Test',
        'account_number' => '0000000000',
    ];

    $this->paymentOptionRequest->merge($this->paymentOptionPayload);

    $this->withdrawalRequest = new WithdrawalRequest();

    $this->withdrawalPayload = [
        'currency' => 'NGN',
        'reason' => 'Test withdrawal',
        'amount' => $this->user->account->balance - 10000,
    ];

    $this->withdrawalRequest->merge($this->withdrawalPayload);

    $this->settings = json_decode(Setting::where('name', 'withdraw')->value('value'));
});

it('should throw an error if payment option required fields are not filled', function () {
    $this->paymentOptionPayload['bank_id'] = '';
    $this->paymentOptionRequest->merge($this->paymentOptionPayload);

    $response = $this->postJson('api/wallets/withdrawals/payment-options', $this->paymentOptionRequest->all());
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
        ->and($data['message'])->toBe('The bank id field is required.');
});

it('should throw an error if payment option account number is invalid', function () {
    $this->paymentOptionPayload['account_number'] = '1111';
    $this->paymentOptionRequest->merge($this->paymentOptionPayload);

    $response = $this->postJson('api/wallets/withdrawals/payment-options', $this->paymentOptionRequest->all());
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
        ->and($data['message'])->toBe('The account number field must be at least 10 characters.');
});

it('should create new payment option', function () {
    $response = $this->postJson('api/wallets/withdrawals/payment-options', $this->paymentOptionRequest->all());
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_CREATED)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::SUCCESS->value)
        ->and($data['message'])->toBe('Bank withdrawal details created')
        ->and($data['data']['bank_id'])->toBe($this->paymentOptionPayload['bank_id'])
        ->and($data['data']['account_name'])->toBe($this->paymentOptionPayload['account_name'])
        ->and($data['data']['account_number'])->toBe($this->paymentOptionPayload['account_number']);
});

it('should retrieve payment option', function () {
    $this->postJson('api/wallets/withdrawals/payment-options', $this->paymentOptionRequest->all());
    $response = $this->getJson('api/wallets/withdrawals/payment-options');
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_OK)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::SUCCESS->value)
        ->and($data['message'])->toBe('Bank withdrawal details retrieved')
        ->and(count($data['data']))->toBeGreaterThan(0);
});

it('should throw an error if payment option is invalid', function () {
    $response = $this->postJson('api/wallets/withdrawals', $this->withdrawalRequest->all());
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
        ->and($data['message'])->toBe('The payment option id field is required.');
});

it('should throw an error if the amount to be withdrawn is lesser than the required minimum', function () {
    $paymentOption = $this->postJson('api/wallets/withdrawals/payment-options',
        $this->paymentOptionRequest->all())->json();

    $this->withdrawalPayload['payment_option_id'] = $paymentOption['data']['id'];
    $this->withdrawalPayload['amount'] = $this->settings->minimum - 1;
    $this->withdrawalRequest->merge($this->withdrawalPayload);

    $response = $this->postJson('api/wallets/withdrawals', $this->withdrawalRequest->all());
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
        ->and($data['message'])->toBe('The minimum amount to be withdrawn is '.number_format($this->settings->minimum).' naira');
});

it('should throw an error if the amount to be withdrawn is greater than the required maximum', function () {
    $paymentOption = $this->postJson('api/wallets/withdrawals/payment-options',
        $this->paymentOptionRequest->all())->json();

    $this->withdrawalPayload['payment_option_id'] = $paymentOption['data']['id'];
    $this->withdrawalPayload['amount'] = $this->settings->maximum + 1;
    $this->withdrawalRequest->merge($this->withdrawalPayload);

    $response = $this->postJson('api/wallets/withdrawals', $this->withdrawalRequest->all());
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
        ->and($data['message'])->toBe('The maximum amount to be withdrawn is '.number_format($this->settings->maximum).' naira');
});

it('should throw an error if user has insufficient balance', function () {
    $paymentOption = $this->postJson('api/wallets/withdrawals/payment-options',
        $this->paymentOptionRequest->all())->json();

    $this->withdrawalPayload['payment_option_id'] = $paymentOption['data']['id'];
    $this->withdrawalPayload['amount'] = $this->user->account->balance + 10000;
    $this->withdrawalRequest->merge($this->withdrawalPayload);

    $response = $this->postJson('api/wallets/withdrawals', $this->withdrawalRequest->all());
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_BAD_REQUEST)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
        ->and($data['message'])->toBe('Insufficient Balance');
});

it('should throw an error if user attempts to send simultaneous withdrawal requests', function () {
    $paymentOption = $this->postJson('api/wallets/withdrawals/payment-options',
        $this->paymentOptionRequest->all())->json();

    $this->withdrawalPayload['payment_option_id'] = $paymentOption['data']['id'];
    $this->withdrawalRequest->merge($this->withdrawalPayload);

    $this->postJson('api/wallets/withdrawals', $this->withdrawalRequest->all());
    $response = $this->postJson('api/wallets/withdrawals', $this->withdrawalRequest->all());
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_TOO_MANY_REQUESTS)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
        ->and($data['message'])->toBe('Double withdrawal spotted. Try again in a minute time.');
});

it('should successfully withdraw from account', function () {
    $reference = $this->user->id.'-'.$this->withdrawalPayload['amount'];
    Redis::del($reference);

    $paymentOption = $this->postJson('api/wallets/withdrawals/payment-options',
        $this->paymentOptionRequest->all())->json();

    $this->withdrawalPayload['payment_option_id'] = $paymentOption['data']['id'];
    $this->withdrawalRequest->merge($this->withdrawalPayload);

    $response = $this->postJson('api/wallets/withdrawals', $this->withdrawalRequest->all());
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_CREATED)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::SUCCESS->value)
        ->and($data['message'])->toBe('Bank withdrawal initiated. Kindly input the 6 digits OTP sent to your mobile phone to finalize the withdrawal')
        ->and($data['data']['amount'])->toBe(number_format($this->withdrawalPayload['amount'], 2))
        ->and($data['data']['account_name'])->toBe($this->paymentOptionPayload['account_name'])
        ->and($data['data']['account_number'])->toBe($this->paymentOptionPayload['account_number']);
});
