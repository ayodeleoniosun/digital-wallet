<?php

namespace App\Domains\Authentication\Http\Controllers;

use App\Domains\Authentication\Actions\CreateUser;
use App\Domains\Authentication\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class RegisterController
{

    public function __construct(public CreateUser $createUser)
    {

    }

    public function create(RegisterRequest $request): JsonResponse
    {
        $response = $this->createUser->execute($request)->toArray();

        return successResponse('Registration successful', Response::HTTP_CREATED, $response);
    }

}
