<?php

namespace Tests\Domains\Wallet\Http\Controllers;

use App\Domains\Utils\Enums\StatusTypesEnum;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Tests\Domains\Wallet\Examples;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->user->profile()->create([
        'user_id' => $this->user->id,
        'unique_id' => uniquePrefix(),
        'phone' => '+2348123456789',
    ]);

    $this->user->account()->create();

    $this->actingAs($this->user);

    $this->request = new Request();

    $this->invalidReference = 'invalidReference';

    Http::fake([
        'https://api.paystack.co/transaction/verify/'.$this->invalidReference => Http::response(Examples::invalidTransactionReference()),
    ]);
});

it('should throw an error if user does not exist while processing deposit webhook event', function () {
    $this->request->merge(Examples::depositWebhookEvent());
    $response = $this->postJson('api/webhooks/deposit', $this->request->all());
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_NOT_FOUND)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
        ->and($data['message'])->toBe('User does not exist');
});

it('should throw an error if deposit status is not success', function () {
    $this->request->merge(Examples::depositWebhookEvent('error', $this->user->email));

    $response = $this->postJson('api/webhooks/deposit', $this->request->all());
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_BAD_REQUEST)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
        ->and($data['message'])->toBe('Deposit not successful from payment provider');
});

it('should throw an error if the deposit reference is invalid', function () {
    $this->request->merge(Examples::depositWebhookEvent('success', $this->user->email, $this->invalidReference));

    $response = $this->postJson('api/webhooks/deposit', $this->request->all());
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_NOT_FOUND)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
        ->and($data['message'])->toBe('Transaction reference not found');
});

it('should credit user account if he has not been previously credited', function () {
    $webhookEvent = Examples::depositWebhookEvent('success', $this->user->email);
    $this->request->merge($webhookEvent);

    $response = $this->postJson('api/webhooks/deposit', $this->request->all());
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_OK)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::SUCCESS->value)
        ->and($data['message'])->toBe('Deposit is being processed');
});


