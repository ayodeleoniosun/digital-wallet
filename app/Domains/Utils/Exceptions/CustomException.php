<?php

namespace App\Domains\Utils\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CustomException extends Exception
{
    public function __construct(
        public $message,
        public int $statusCode = Response::HTTP_BAD_REQUEST,
    ) {
    }

    public function report(): bool
    {
        return true;
    }

    public function render(): JsonResponse
    {
        return errorResponse($this->message, $this->statusCode);
    }
}
