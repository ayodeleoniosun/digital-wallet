<?php

namespace Tests\Domains\Authentication\Http\Controllers;

use App\Domains\Authentication\Http\Requests\RegisterRequest;
use App\Domains\Utils\Enums\StatusTypesEnum;
use Illuminate\Http\Response;

beforeEach(function () {
    $this->request = new RegisterRequest();

    $this->payload = [
        'firstname' => 'John',
        'lastname' => 'Doe',
        'phone' => '+2348123456789',
        'email' => fake()->email,
        'password' => 'Bumpa@2024',
    ];

    $this->request->merge($this->payload);
});

describe('User Registration', function () {
    it('should return a validation error if all required fields are not filled', function () {
        $this->payload['firstname'] = '';
        $this->request->merge($this->payload);

        $response = $this->postJson('api/auth/register', $this->request->all());
        $data = $response->json();

        expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->and($data)->toBeArray()
            ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
            ->and($data['message'])->toBe('The firstname field is required.');
    });

    it('should return a validation error if phone number is invalid', function () {
        $this->payload['phone'] = 'Invalid phone';
        $this->request->merge($this->payload);

        $response = $this->postJson('api/auth/register', $this->request->all());
        $data = $response->json();

        expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->and($data)->toBeArray()
            ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
            ->and($data['message'])->toBe('The phone field format is invalid.');
    });

    it('should return a validation error if email address is invalid', function () {
        $this->payload['email'] = 'Invalid email address';
        $this->request->merge($this->payload);

        $response = $this->postJson('api/auth/register', $this->request->all());
        $data = $response->json();

        expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->and($data)->toBeArray()
            ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
            ->and($data['message'])->toBe('The email field must be a valid email address.');
    });

    it('should return a validation error if password strength is weak', function () {
        $this->payload['password'] = 'password';
        $this->request->merge($this->payload);

        $response = $this->postJson('api/auth/register', $this->request->all());
        $data = $response->json();

        expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->and($data)->toBeArray()
            ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
            ->and($data['message'])->toBe('The password field must contain at least one uppercase and one lowercase letter.');
    });

    it('should return a validation error if phone already exist', function () {
        $this->postJson('api/auth/register', $this->request->all());
        $response = $this->postJson('api/auth/register', $this->request->all());
        $data = $response->json();

        expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->and($data)->toBeArray()
            ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
            ->and($data['message'])->toBe('The phone has already been taken.');
    });

    it('should return a validation error if email address already exist', function () {
        $this->postJson('api/auth/register', $this->request->all());

        $this->payload['phone'] = '+2348123456781';
        $this->request->merge($this->payload);

        $response = $this->postJson('api/auth/register', $this->request->all());
        $data = $response->json();

        expect($response->status())->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->and($data)->toBeArray()
            ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
            ->and($data['message'])->toBe('The email has already been taken.');
    });

    it('can register user if all fields are valid', function () {
        $response = $this->postJson('api/auth/register', $this->request->all());
        $data = $response->json();

        expect($response->status())->toBe(Response::HTTP_CREATED)
            ->and($data)->toBeArray()
            ->and($data['status'])->toBe(StatusTypesEnum::SUCCESS->value)
            ->and($data['message'])->toBe('Registration successful')
            ->and($data['data']['firstname'])->toBe($this->request->firstname)
            ->and($data['data']['lastname'])->toBe($this->request->lastname)
            ->and($data['data']['email'])->toBe($this->request->email)
            ->and($data['data']['id'])->toBe($response['data']['profile']['user_id'])
            ->and($data['data']['profile']['phone'])->toBe($this->request->phone);
    });
});

describe('User Login', function () {
    it('should return an error if user does not exist', function () {
        $response = $this->postJson('api/auth/login', $this->request->all());
        $data = $response->json();

        expect($response->status())->toBe(Response::HTTP_NOT_FOUND)
            ->and($data)->toBeArray()
            ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
            ->and($data['message'])->toBe('User does not exist.');
    });

    it('should return an error if login credentials is invalid', function () {
        $this->postJson('api/auth/register', $this->request->all());

        $this->payload['password'] = 'invalidPassword';
        $this->request->merge($this->payload);

        $response = $this->postJson('api/auth/login', $this->request->all());
        $data = $response->json();

        expect($response->status())->toBe(Response::HTTP_BAD_REQUEST)
            ->and($data)->toBeArray()
            ->and($data['status'])->toBe(StatusTypesEnum::ERROR->value)
            ->and($data['message'])->toBe('Incorrect login credentials.');
    });

    it('should login if login credentials is invalid', function () {
        $this->postJson('api/auth/register', $this->request->all());

        $response = $this->postJson('api/auth/login', $this->request->all());
        $data = $response->json();

        expect($response->status())->toBe(Response::HTTP_OK)
            ->and($data)->toBeArray()
            ->and($data['status'])->toBe(StatusTypesEnum::SUCCESS->value)
            ->and($data['message'])->toBe('Login successful')
            ->and($data['data']['token'])->not()->toBeEmpty()
            ->and($data['data']['firstname'])->toBe($this->request->firstname)
            ->and($data['data']['lastname'])->toBe($this->request->lastname)
            ->and($data['data']['email'])->toBe($this->request->email)
            ->and($data['data']['phone_number'])->toBe($this->request->phone);
    });
});
