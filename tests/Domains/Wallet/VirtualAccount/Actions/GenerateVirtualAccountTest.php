<?php

namespace Tests\Domains\Wallet\VirtualAccount\Actions;

use App\Domains\Utils\Enums\ActivityTypesEnum;
use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Wallet\VirtualAccount\Actions\GenerateVirtualAccount;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->user->profile()->create([
        'user_id' => $this->user->id,
        'unique_id' => uniquePrefix(),
        'phone' => '+2348123456789',
    ]);

    $this->actingAs($this->user);
});

it("can create generate a virtual account details", function () {
    $response = (new GenerateVirtualAccount())->execute();

    expect($response->firstname)->toBe($this->user->firstname)
        ->and($response->lastname)->toBe($this->user->lastname)
        ->and($response->email)->toBe($this->user->email);

    $this->assertDatabaseHas('virtual_accounts', [
        'user_id' => $this->user->id,
    ]);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $response->id,
        'type' => ActivityTypesEnum::CREATE_VIRTUAL_ACCOUNT->value,
    ]);
});

it("should throw an error if virtual account details has already been generated", function () {
    (new GenerateVirtualAccount())->execute();

    (new GenerateVirtualAccount())->execute();

    $this->assertDatabaseMissing('virtual_accounts', [
        'user_id' => $this->user->id,
    ]);

    $this->assertDatabaseMissing('activity_logs', [
        'user_id' => $this->user->id,
        'type' => ActivityTypesEnum::CREATE_VIRTUAL_ACCOUNT->value,
    ]);

})->throws(CustomException::class, 'Virtual account details already generated');
