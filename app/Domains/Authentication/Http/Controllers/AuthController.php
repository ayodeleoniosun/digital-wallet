<?php

namespace App\Domains\Authentication\Http\Controllers;

use App\Domains\Authentication\Actions\CreateUser;
use App\Domains\Authentication\Actions\LoginUser;
use App\Domains\Authentication\Http\Requests\RegisterRequest;
use App\Domains\Utils\Exceptions\CustomException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController
{

    public function __construct(public CreateUser $createUser, public LoginUser $loginUser)
    {

    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $response = $this->createUser->execute($request)->toArray();

        return success('Registration successful', Response::HTTP_CREATED, $response);
    }

    /**
     * @throws CustomException
     */
    public function login(Request $request): JsonResponse
    {
        $response = $this->loginUser->execute($request);

        return success('Login successful', Response::HTTP_OK, $response);
    }

}
