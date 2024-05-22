<?php

namespace Tests\Domains\Authentication\Actions;

use App\Domains\Authentication\Actions\CreateUser;
use App\Domains\Authentication\Http\Requests\RegisterRequest;
use App\Domains\Utils\Enums\ActivityTypesEnum;

it("can create a new user", function () {
    $this->request = new RegisterRequest();

    $this->request->merge([
        'firstname' => 'John',
        'lastname' => 'Doe',
        'phone' => '+2348123456789',
        'email' => fake()->email,
        'password' => 'password',
    ]);

    $response = (new CreateUser())->execute($this->request);

    expect($response->firstname)->toBe($this->request->firstname)
        ->and($response->lastname)->toBe($this->request->lastname)
        ->and($response->email)->toBe($this->request->email)
        ->and($response->profile->phone)->toBe($this->request->phone);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $response->id,
        'type' => ActivityTypesEnum::REGISTER->value,
    ]);
});
