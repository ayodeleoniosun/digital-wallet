<?php

use App\Domains\Utils\Enums\StatusTypesEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

function success(
    string $message = '',
    int $statusCode = Response::HTTP_OK,
    array|LengthAwarePaginator $data = [],
): JsonResponse {
    return response()->json([
        'status' => StatusTypesEnum::SUCCESS->value,
        'message' => $message,
        'data' => $data,
    ], $statusCode);
}

function errorResponse(string $message = '', int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
{
    return response()->json([
        'status' => StatusTypesEnum::ERROR->value,
        'message' => $message,
    ], $statusCode);
}

function uniquePrefix(): string
{
    return 'BUMPA_'.strtoupper(Str::random(10));
}
