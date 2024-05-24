<?php

namespace Tests\Domains\Wallet\Withdrawal\Actions;

use App\Domains\Utils\Enums\ActivityTypesEnum;
use App\Domains\Utils\Exceptions\CustomException;
use App\Domains\Wallet\Withdrawal\Actions\CreatePaymentOption;
use App\Domains\Wallet\Withdrawal\Http\Requests\PaymentOptionRequest;
use App\Models\User;
use Database\Seeders\BanksSeeder;

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->user->profile()->create([
        'user_id' => $this->user->id,
        'unique_id' => uniquePrefix(),
        'phone' => '+2348123456789',
    ]);

    $this->actingAs($this->user);

    $this->seed(BanksSeeder::class);

    $this->request = new PaymentOptionRequest();

    $this->payload = [
        'bank_id' => 160, //zenith bank
        'account_name' => 'Test',
        'account_number' => '0000000000',
    ];

    $this->request->merge($this->payload);

});

it("should create a new payment option", function () {
    $response = (new CreatePaymentOption())->execute($this->request);

    expect($response->bank_id)->toBe($this->payload['bank_id'])
        ->and($response->account_name)->toBe($this->payload['account_name'])
        ->and($response->account_number)->toBe($this->payload['account_number']);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $response->user_id,
        'type' => ActivityTypesEnum::PAYMENT_OPTION_CREATED->value,
    ]);
});

it("should throw an error if payment option has already been created", function () {
    (new CreatePaymentOption())->execute($this->request);

    (new CreatePaymentOption())->execute($this->request);

})->throws(CustomException::class, 'Payment option already added');
