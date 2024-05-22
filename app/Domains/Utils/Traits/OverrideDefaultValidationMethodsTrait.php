<?php

namespace App\Domains\Utils\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

trait OverrideDefaultValidationMethodsTrait
{
    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], Response::HTTP_UNPROCESSABLE_ENTITY)
            );
        }

        parent::failedValidation($validator);
    }
}
