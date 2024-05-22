<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

function successResponse(
    string $message = '',
    int $statusCode = Response::HTTP_OK,
    array|LengthAwarePaginator $data = [],
): JsonResponse {
    return response()->json([
        'status' => 'success',
        'message' => $message,
        'data' => $data,
    ], $statusCode);
}

function errorResponse(string $message = '', int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
{
    return response()->json([
        'status' => 'error',
        'message' => $message,
    ], $statusCode);
}
