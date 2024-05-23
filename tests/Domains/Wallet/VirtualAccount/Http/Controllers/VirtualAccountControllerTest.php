<?php

namespace Tests\Domains\Wallet\VirtualAccount\Http\Controllers;

use App\Domains\Utils\Enums\ActivityTypesEnum;
use App\Domains\Utils\Enums\StatusTypesEnum;
use App\Models\User;
use Illuminate\Http\Response;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->user->profile()->create([
        'user_id' => $this->user->id,
        'unique_id' => uniquePrefix(),
        'phone' => '+2348123456789',
    ]);

    $this->actingAs($this->user);
});

it('should generate virtual account details', function () {
    $response = $this->postJson('api/wallets/virtual-accounts');
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_CREATED)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::SUCCESS->value)
        ->and($data['message'])->toBe('Virtual account details successfully generated')
        ->and($data['data']['firstname'])->toBe($this->user->firstname)
        ->and($data['data']['lastname'])->toBe($this->user->lastname)
        ->and($data['data']['email'])->toBe($this->user->email);

    $this->assertDatabaseHas('virtual_accounts', [
        'user_id' => $this->user->id,
    ]);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $this->user->id,
        'type' => ActivityTypesEnum::CREATE_VIRTUAL_ACCOUNT->value,
    ]);
});

it('should throw an error if virtual account details exist', function () {
    $this->postJson('api/wallets/virtual-accounts');
    $response = $this->postJson('api/wallets/virtual-accounts');

    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_BAD_REQUEST)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
        ->and($data['message'])->toBe('Virtual account details already generated');
});

it('should retrieve virtual account details', function () {
    $response = $this->getJson('api/wallets/virtual-accounts');
    $data = $response->json();

    expect($response->status())->toBe(Response::HTTP_OK)
        ->and($data)->toBeArray()
        ->and($data['status'])->toBe(StatusTypesEnum::SUCCESS->value)
        ->and($data['message'])->toBe('Virtual account details successfully retrieved')
        ->and(count($data['data']))->toBeGreaterThan(0)
        ->and($data['data'])->each(fn($item) => $item->user_id->toBe($this->user->id));
});
