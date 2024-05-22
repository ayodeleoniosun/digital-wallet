<?php

namespace Tests\Domains\Authentication\Actions;

use App\Domains\Authentication\Actions\CreateUser;
use App\Domains\Authentication\Actions\LoginUser;
use App\Domains\Authentication\Http\Requests\RegisterRequest;
use App\Domains\Utils\Enums\ActivityTypesEnum;
use App\Domains\Utils\Exceptions\CustomException;

it("should throw an error if the user does not exist", function () {
    $this->request = new RegisterRequest();

    $this->request->merge([
        'email' => fake()->email,
        'password' => 'password',
    ]);

    (new LoginUser())->execute($this->request);
})->throws(CustomException::class, 'User does not exist');

it("should throw an error if the login details is invalid", function () {
    $this->request = new RegisterRequest();

    $this->request->merge([
        'firstname' => 'John',
        'lastname' => 'Doe',
        'phone' => '+2348123456789',
        'email' => fake()->email,
        'password' => 'password',
    ]);

    (new CreateUser())->execute($this->request);

    $this->request['password'] = 'invalidPassword';

    (new LoginUser())->execute($this->request);

})->throws(CustomException::class, 'Incorrect login credentials.');

it("can login a user if the login credentials is valid", function () {
    $this->request = new RegisterRequest();

    $this->request->merge([
        'firstname' => 'John',
        'lastname' => 'Doe',
        'phone' => '+2348123456789',
        'email' => fake()->email,
        'password' => 'password',
    ]);

    (new CreateUser())->execute($this->request);

    $response = (new LoginUser())->execute($this->request);

    expect($response['token'])->not()->toBeEmpty()
        ->and($response['firstname'])->toBe($this->request->firstname)
        ->and($response['lastname'])->toBe($this->request->lastname)
        ->and($response['email'])->toBe($this->request->email)
        ->and($response['phone_number'])->toBe($this->request->phone);

    $this->assertDatabaseHas('activity_logs', [
        'user_id' => $response['id'],
        'type' => ActivityTypesEnum::LOGIN->value,
    ]);
});
