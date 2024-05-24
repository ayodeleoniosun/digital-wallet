<?php

namespace Tests\Domains\Wallet\Withdrawal\Actions;

use App\Domains\Wallet\Withdrawal\Actions\CreatePaymentOption;
use App\Domains\Wallet\Withdrawal\Actions\GetPaymentOption;
use App\Domains\Wallet\Withdrawal\Http\Requests\PaymentOptionRequest;
use App\Models\Bank;
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
        'bank_id' => Bank::query()->inRandomOrder()->value('id'),
        'account_name' => fake()->firstName.' '.fake()->lastName,
        'account_number' => '11111111111',
    ];

    $this->request->merge($this->payload);

});

it("should retrieve payment option", function () {
    (new CreatePaymentOption())->execute($this->request);

    $response = (new GetPaymentOption())->execute();

    expect($response->bank_id)->toBe($this->payload['bank_id'])
        ->and($response->account_name)->toBe($this->payload['account_name'])
        ->and($response->account_number)->toBe($this->payload['account_number']);
});
