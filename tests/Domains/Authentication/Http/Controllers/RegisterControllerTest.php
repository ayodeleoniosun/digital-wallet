<?php

namespace Tests\Domains\Authentication\Http\Controllers;

use App\Domains\Authentication\Http\Requests\RegisterRequest;

beforeEach(function () {
    $this->request = new RegisterRequest();

    $this->payload = [
        'firstname' => 'John',
        'lastname' => 'Doe',
        'phone' => '+2348123456789',
        'email' => fake()->email,
        'password' => 'password',
    ];

    $this->request->merge($this->payload);
});

it('cannot register user if all required fields are not filled', function () {
    $this->payload['firstname'] = '';
    $this->request->merge($this->payload);

    $response = $this->postJson('api/auth/register', $this->request->all())->json();

    expect($response)->toBeArray()
        ->and($response['status'])->toBe('error')
        ->and($response['message'])->toBe('The firstname field is required.');
});

it('cannot register user if phone number is invalid', function () {
    $this->payload['phone'] = 'Invalid phone';
    $this->request->merge($this->payload);

    $response = $this->postJson('api/auth/register', $this->request->all())->json();

    expect($response)->toBeArray()
        ->and($response['status'])->toBe('error')
        ->and($response['message'])->toBe('The phone field format is invalid.');
});

it('cannot register user if email address is invalid', function () {
    $this->payload['email'] = 'Invalid email address';
    $this->request->merge($this->payload);

    $response = $this->postJson('api/auth/register', $this->request->all())->json();

    expect($response)->toBeArray()
        ->and($response['status'])->toBe('error')
        ->and($response['message'])->toBe('The email field must be a valid email address.');
});

it('cannot register user if password strength is weak', function () {
    $response = $this->postJson('api/auth/register', $this->request->all())->json();

    expect($response)->toBeArray()
        ->and($response['status'])->toBe('error')
        ->and($response['message'])->toBe('The password field must contain at least one uppercase and one lowercase letter.');
});

it('cannot register user if phone already exist', function () {
    $this->payload['password'] = 'Bumpa@2024';
    $this->request->merge($this->payload);

    $this->postJson('api/auth/register', $this->request->all())->json();
    $response = $this->postJson('api/auth/register', $this->request->all())->json();

    expect($response)->toBeArray()
        ->and($response['status'])->toBe('error')
        ->and($response['message'])->toBe('The phone has already been taken.');
});

it('cannot register user if email address already exist', function () {
    $this->payload['password'] = 'Bumpa@2024';
    $this->request->merge($this->payload);

    $this->postJson('api/auth/register', $this->request->all())->json();

    $this->payload['phone'] = '+2348123456781';
    $this->request->merge($this->payload);

    $response = $this->postJson('api/auth/register', $this->request->all())->json();

    expect($response)->toBeArray()
        ->and($response['status'])->toBe('error')
        ->and($response['message'])->toBe('The email has already been taken.');
});

it('can register user if all fields are valid', function () {
    $this->payload['password'] = 'Bumpa@2024';
    $this->request->merge($this->payload);

    $response = $this->postJson('api/auth/register', $this->request->all())->json();

    expect($response)->toBeArray()
        ->and($response['status'])->toBe('success')
        ->and($response['message'])->toBe('Registration successful')
        ->and($response['data']['firstname'])->toBe($this->request->firstname)
        ->and($response['data']['lastname'])->toBe($this->request->lastname)
        ->and($response['data']['email'])->toBe($this->request->email)
        ->and($response['data']['id'])->toBe($response['data']['profile']['user_id'])
        ->and($response['data']['profile']['phone'])->toBe($this->request->phone);
});
