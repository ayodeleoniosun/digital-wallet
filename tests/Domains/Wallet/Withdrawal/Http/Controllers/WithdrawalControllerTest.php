<?php

namespace Tests\Domains\Wallet\Withdrawal\Http\Controllers;

use App\Domains\Utils\Enums\StatusTypesEnum;
use App\Domains\Wallet\Withdrawal\Http\Requests\PaymentOptionRequest;
use App\Models\Bank;
use App\Models\User;
use Database\Seeders\BanksSeeder;
use Illuminate\Http\Response;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->user->profile()->create([
        'user_id' => $this->user->id,
        'unique_id' => uniquePrefix(),
        'phone' => '+2348123456789',
    ]);

    $this->user->account()->create();

    $this->actingAs($this->user);

    $this->seed(BanksSeeder::class);

    $this->request = new PaymentOptionRequest();

    $this->payload = [
        'bank_id' => Bank::query()->inRandomOrder()->value('id'),
        'account_name' => fake()->firstName.' '.fake()->lastName,
        'account_number' => '11111111111',
    ];

    $this->request->merge($this->payload);
});

it('should throw an error if payment option required fields are not filled', function () {
    $this->payload['bank_id'] = '';
    $this->request->merge($this->payload);

    $response = $this->postJson('api/wallets/withdrawals/payment-options', $this->request->all());
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
        ->and($data['message'])->toBe('The bank id field is required.');
});

it('should throw an error if payment option account number is invalid', function () {
    $this->payload['account_number'] = '1111';
    $this->request->merge($this->payload);

    $response = $this->postJson('api/wallets/withdrawals/payment-options', $this->request->all());
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
        ->and($data['message'])->toBe('The account number field must be 11 digits.');
});

it('should create new payment option', function () {
    $response = $this->postJson('api/wallets/withdrawals/payment-options', $this->request->all());
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_CREATED)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::SUCCESS->value)
        ->and($data['message'])->toBe('Bank withdrawal details created')
        ->and($data['data']['bank_id'])->toBe($this->payload['bank_id'])
        ->and($data['data']['account_name'])->toBe($this->payload['account_name'])
        ->and($data['data']['account_number'])->toBe($this->payload['account_number']);
});

it('should retrieve payment option', function () {
    $this->postJson('api/wallets/withdrawals/payment-options', $this->request->all());
    $response = $this->getJson('api/wallets/withdrawals/payment-options');
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_OK)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::SUCCESS->value)
        ->and($data['message'])->toBe('Bank withdrawal details retrieved')
        ->and($data['data']['bank_id'])->toBe($this->payload['bank_id'])
        ->and($data['data']['account_name'])->toBe($this->payload['account_name'])
        ->and($data['data']['account_number'])->toBe($this->payload['account_number']);
});
