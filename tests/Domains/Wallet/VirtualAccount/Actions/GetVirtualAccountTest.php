<?php

namespace Tests\Domains\Wallet\VirtualAccount\Actions;

use App\Domains\Wallet\VirtualAccount\Actions\GetVirtualAccount;
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

it("can get virtual account details", function () {
    $response = (new GetVirtualAccount())->execute();

    expect($response)->each(fn($item) => $item->user_id->toBe($this->user->id))
        ->and(count($response))->toBeGreaterThan(0);
});
