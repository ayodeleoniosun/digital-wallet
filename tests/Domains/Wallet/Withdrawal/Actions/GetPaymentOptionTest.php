<?php

namespace Tests\Domains\Wallet\Withdrawal\Actions;

use App\Domains\Wallet\Withdrawal\Actions\CreatePaymentOption;
use App\Domains\Wallet\Withdrawal\Actions\GetPaymentOption;
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

it("should retrieve payment option", function () {
    (new CreatePaymentOption())->execute($this->request);

    $response = (new GetPaymentOption())->execute();

    expect($response->count())->toBeGreaterThan(0)
        ->and($response)->each(fn($item) => $item->user_id->toBe($this->user->id))
        ->and($response)->each(fn($item) => $item->bank_id->toBe($this->payload['bank_id']));
});
